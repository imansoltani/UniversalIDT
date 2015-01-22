<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class RequestTypeEnumType extends AbstractEnumType
{
    const CREATION = "C";
    const ACTIVATION = "A";
    const RECHARGE = "R";
    const DEACTIVATION = "D";

    protected static $choices = [
        self::CREATION => 'Creation',
        self::ACTIVATION    => 'Activation',
        self::RECHARGE  => 'Recharge',
        self::DEACTIVATION    => 'Deactivation',
    ];
}