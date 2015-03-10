<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\Entity\Destination;
use Universal\IDTBundle\Entity\Product;
use Universal\IDTBundle\Entity\Rate;

class ImportRatesCommand extends AbstractImportCommand
{
    protected function configure()
    {
        $this
            ->setName('import:rates')
            ->setDescription('import rates from CSV files in app/Resources/Import/CSV/Rates/*.csv')
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear rate entity.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get("doctrine.orm.default_entity_manager");

        if ($input->getOption('clear')) {
            $em->createQueryBuilder()
                ->delete('UniversalIDTBundle:Rate', 'rate')
                ->getQuery()
                ->execute();

            $output->writeln("Rate entity cleared.");
        }

        $classIdsDone = array();

        $j = 0;
        foreach(scandir($this->getFileAddressName("CSV/Rates")) as $fileName) {
            if ($fileName != "." && $fileName != "..")
            {
                $countryISO = substr($fileName, 7, 2);
                $productName = rtrim(substr($fileName, 10, -7));
                $type = ltrim(substr($fileName, -7, -4));
//                echo $fileName . " -> '" . $countryISO . "', '" . $productName . "', '" . $type . "'\r\n";

                /** @var Product $product */
                $product = $em->getRepository('UniversalIDTBundle:Product')->findOneBy(array(
                        'countryISO' => $countryISO,
                        'name' => $productName
                    ));

                if (!$product) {
                    echo "Error in file '" . $fileName . "': Product Not Found.\r\n";
                    continue;
                }

                if (in_array($product->getClassId(), $classIdsDone)) {
                    echo "Error in file '" . $fileName . "': Product Not Found.\r\n";
                    continue;
                }

                $classIdsDone []= $product->getClassId();

                //----------------
                $data = $this->csv_to_array($this->getFileAddressName("CSV/Rates", $fileName), ',');

                $i = 0;
                foreach($data as $row) {
                    if($row['iso'] == "" || $row['country'] == "" || $row['location'] == "" || $row['connection_fee'] == "" || $row['rate'] == "")
                        continue;

                    $destination = $em->getRepository('UniversalIDTBundle:Destination')->findOneBy(array(
                            'countryIso' => $row['iso'],
                            'location' => $row['location'] != '#VALUE!' ?: null
                        ));

                    if(!$destination) {
                        $destination = new Destination();
                        $destination->setCountryIso($row['iso']);
                        $destination->setLocation($row['location'] != '#VALUE!' ?: null);
                        $em->persist($destination);

                        echo "Destination Created: " . $row['iso'] . " " . $row['location'] . "\r\n";
                        $em->flush();
                    }

                    $rate = new Rate();
                    $rate->setClassId($product->getClassId());
                    $rate->setConnectionFees($row['connection_fee']);
                    $rate->setCost($row['rate']);
                    $rate->setDestination($destination);
                    $rate->setType($type);

                    $em->persist($rate);
                    $em->flush();
                    $i++;
                }

                echo "File $fileName done. (0 rates)\r\n";
            }
        }

        $output->writeln("done ($j rates)");
    }
}