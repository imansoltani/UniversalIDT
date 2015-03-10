<?php
namespace Universal\IDTBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

abstract class AbstractImportCommand extends ContainerAwareCommand
{
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

    protected function getFileAddressName($dir, $fileName = null)
    {
        return getcwd()."/app/Resources/Import/".$dir.($fileName ? "/".$fileName : "");
    }
}