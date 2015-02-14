<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class RateEnumType extends AbstractEnumType
{
    const TOLL_FREE = "TF";
    const LOCAL_ACCESS = "LAC";

    protected static $choices = [
        self::TOLL_FREE => 'Toll Free',
        self::LOCAL_ACCESS    => 'Local Access',
    ];
}