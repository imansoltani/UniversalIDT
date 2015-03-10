<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\Entity\Destination;

class ImportDestinationsCommand extends AbstractImportCommand
{
    protected function configure()
    {
        $this
            ->setName('import:destinations')
            ->setDescription('import destinations from CSV in app/Resources/Import/CSV/destinations.csv')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get("doctrine.orm.default_entity_manager");

        $data = $this->csv_to_array($this->getFileAddressName("CSV", "destinations.csv"));

        $i = 0;
        foreach($data as $row) {
            if(!$destination = $em->getRepository("UniversalIDTBundle:Destination")->findOneBy(array("countryIso"=>$row['ISO'],"location"=>null))) {
                $destination = new Destination();
                $destination->setCountryIso($row['ISO']);
                $em->persist($destination);
            }
            $i++;
        }
        $em->flush();

        $output->writeln("done ($i destinations)");

    }
}