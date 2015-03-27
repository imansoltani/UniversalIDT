<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\Entity\Vat;

class ImportVatsCommand extends AbstractImportCommand
{
    protected function configure()
    {
        $this
            ->setName('import:vats')
            ->setDescription('import vats from CSV in app/Resources/Import/CSV/vats.csv')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get("doctrine.orm.default_entity_manager");

        $data = $this->csv_to_array($this->getFileAddressName("CSV", "vats.csv"));

        $i = 0;
        foreach($data as $row) {
            if(!$vat = $em->getRepository("UniversalIDTBundle:Vat")->findOneBy(array("countryISO"=>$row['countryISO']))) {
                $vat = new Vat();
                $vat->setCountryISO($row['countryISO']);
                $em->persist($vat);
            }
            $vat->setValue($row['value']);
            $i++;
        }
        $em->flush();

        $output->writeln("done ($i vats)");
    }
}