<?php

namespace Universal\IDTBundle\Idt;

class Log
{
    public static function save($data, $status)
    {
        $filename =  getcwd()."/uploads/test/logs/".round(microtime(true)*1000)."-".$status.".txt";
        file_put_contents($filename, $data);
    }
}