<?php

namespace Universal\IDTBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Universal\IDTBundle\Entity\Destination;

class ImportDestinationsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('import:destinations')
            ->setDescription('import destinations from CSV in app/Resources/Import/CSV/destinations.csv')
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

        $data = $this->csv_to_array($this->getFile("CSV", "destinations.csv"));

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