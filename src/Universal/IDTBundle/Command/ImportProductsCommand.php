<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Json\JsonParser;

class ImportProductsCommand extends AbstractImportCommand
{
    protected function configure()
    {
        $this
            ->setName('import:products')
            ->setDescription('import Product from CSV in app/Resources/Import/CSV/products.csv')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear product entity.')
            ->addOption('test', null, InputOption::VALUE_NONE, 'import Test Products.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get("doctrine.orm.default_entity_manager");

        if ($input->getOption('clear')) {
            $em->createQueryBuilder()
                ->delete('UniversalIDTBundle:Product', 'product')
                ->getQuery()
                ->execute();

            $output->writeln("Product entity cleared.");
        }

        $accessNumbers = $this->groupAccessNumbersByClassId($this->csv_to_array($this->getFileAddressName("CSV", "access_numbers.csv")));

        $data = $this->csv_to_array($this->getFileAddressName("CSV", $input->getOption('test') ? "products_test.csv" : "products.csv"));

        $images_path = $this->copyImages();

        $i = 0;
        foreach($data as $row) {
            $product = new Product();
            $product->setName($row['name']);
            $product->setCountryISO($row['country']);
            $product->setCurrency($row['currency']);
            $product->setClassId($row['cid']);
            $product->setDenominations(array($row['denom1'],$row['denom2'],$row['denom3']));
            $product->setFreeAmountDenomination1($row['free_amount_denom1']);

            if (isset($accessNumbers[$row['cid']])) {
                $product->setAllAccessNumbers($accessNumbers[$row['cid']]);
            }
            else {
                $output->writeln("warning: '" . $row['country'] . " " . $row['name'] . "' don't has access number.");
            }

            $imageFileName = strtoupper($row['country']." ".$row['name']).".png";
            if (file_exists($images_path."/".$imageFileName)) {
                $product->setFile(new UploadedFile($images_path."/".$imageFileName, $imageFileName, 'image/png', 1, null, true));
            }
            else {
                $output->writeln("warning: '" . $row['country'] . " " . $row['name'] . "' don't has image.");
            }

//            $product->setDialingInstructions();
//            $product->setGeneralInformation();
            $em->persist($product);
            $i++;
        }
        $em->flush();

        $output->writeln("done ($i products)");

    }

    private function groupAccessNumbersByClassId($array)
    {
        $result = array();
        foreach ($array as $row) {
            if($row['ClassID'] == "" || $row['ClassID'] == "x")
                continue;

            if(!isset($result[$row['ClassID']]))
                $result[$row['ClassID']] = array();

            //number,location,type,Languages
            $result[$row['ClassID']] []= array(
                JsonParser::ACCESS_NUMBERS_TYPE => trim($row['Type']),
                JsonParser::ACCESS_NUMBERS_NUMBER => trim($row['Number']),
                JsonParser::ACCESS_NUMBERS_LOCATION => trim($row['Location']),
                JsonParser::ACCESS_NUMBERS_LANGUAGES => trim($this->fixLanguages($row['Language']))
            );
        }

        return $result;
    }

    private function fixLanguages($string)
    {
        $string = trim($string, " \t\n\r\0\x0B;");
        $string = str_replace(";", ",", $string);
        $string = strtoupper($string);

        return $string;
    }

    private function copyImages()
    {
        $src = $this->getFileAddressName("Image");
        $dst = getcwd()."/web/uploads/test/image_temp";

        $dir = opendir($src);
        @mkdir($dst);

        while(false !== ( $file = readdir($dir)) )
            if (( $file != '.' ) && ( $file != '..' ))
                copy($src . '/' . $file,$dst . '/' . $file);

        closedir($dir);

        return $dst;
    }
}