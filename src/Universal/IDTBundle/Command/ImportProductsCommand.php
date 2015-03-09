<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\Entity\Product;

class ImportProductsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:products')
            ->setDescription('import Product from CSV in app/Resources/Import/CSV/products.csv')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear product entity.')
        ;
    }

    protected function csv_to_array($filename='', $delimiter=';')
    {
        if(!file_exists($filename) || !is_readable($filename))
            return FALSE;
        $header = NULL;
        $data = array();
        if (($handle = fopen($filename, 'r')) !== FALSE)
        {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
            {
                if(!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    private function getFile($dir, $fileName)
    {
        return getcwd()."/app/Resources/Import/".$dir."/".$fileName;
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

        $data = $this->csv_to_array($this->getFile("CSV", "products.csv"));

        $i = 0;
        foreach($data as $row) {
            $product = new Product();
            $product->setName($row['name']);
            $product->setCountryISO($row['country']);
            $product->setCurrency($row['currency']);
            $product->setClassId($row['cid']);
            $product->setDenominations(array($row['denom1'],$row['denom2'],$row['denom3']));
            $product->setFreeAmountDenomination1($row['free_amount_denom1']);
            $em->persist($product);
            $i++;
        }
        $em->flush();

        $output->writeln("done ($i products)");

    }
}