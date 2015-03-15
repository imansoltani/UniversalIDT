<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\DBAL\Types\RateEnumType;
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
        if( "dev" == $this->getContainer()->get('kernel')->getEnvironment()) {
            $output->writeln("Run this command with --env=prod");
            return;
        }

        /** @var EntityManager $em */
        $em = $this->getContainer()->get("doctrine.orm.default_entity_manager");

        if ($input->getOption('clear')) {
            $em->createQueryBuilder()
                ->delete('UniversalIDTBundle:Rate', 'rate')
                ->getQuery()
                ->execute();

            $output->writeln("Rate entity cleared.");
        }

        $matching_list = $this->csv_to_array($this->getFileAddressName("CSV", "matching_cid_and_rates.csv"));

        $class_ids_donned = array();

        $i = 0;
        foreach($matching_list as $matching) {
            if($matching['CID'] == "") {
                $output->writeln("ERROR: '".$matching['Nom']."' doesn't have classID.");
                continue;
            }

            if(in_array($matching['CID'], $class_ids_donned)) {
                $output->writeln("WARNING: '".$matching['Nom']."' has duplicate classID: ".$matching['CID']);
                continue;
            }

            if($matching['TF'] != "") {
                $count = $this->import_rate($matching['CID'], $matching['TF'], RateEnumType::TOLL_FREE, $em);
                $i ++;

                $output->writeln("$i- '" . $matching['TF'] . "' done ($count rates)");
            }

            if($matching['LAC'] != "") {
                $count = $this->import_rate($matching['CID'], $matching['LAC'], RateEnumType::LOCAL_ACCESS, $em);
                $i ++;

                $output->writeln("$i- '" . $matching['LAC'] . "' done ($count rates)");
            }

            $class_ids_donned []= $matching['CID'];

            gc_collect_cycles();
        }

        $output->writeln("completed. ($i files)");
    }

    private function import_rate($class_id, $fileName, $type, EntityManager $em)
    {
        $data = $this->csv_to_array($this->getFileAddressName("CSV/Rates", $fileName), ',');

        if(!$data)
            return 0;

        $i = 0;
        foreach($data as $row) {
            if($row['iso'] == "" || $row['country'] == "" || $row['location'] == "" || $row['connection_fee'] == "" || $row['rate'] == "")
                continue;

            $rate = new Rate();
            $rate->setClassId($class_id);
            $rate->setConnectionFees($row['connection_fee']);
            $rate->setCost($row['rate']);
            $rate->setType($type);
            $rate->setLocation($row['location'] == "0" ? null : $row['location']);
            $rate->setCountryIso($row['iso']);
            $rate->setCountryName($row['country']);

            $em->persist($rate);
            $i++;
        }

        $em->flush();
        $em->clear();
        return $i;
    }
}