<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class PinStatusEnumType extends AbstractEnumType
{
    const PROVISION = "P";
    const ACTIVE = "A";
    const INACTIVE = "I";

    protected static $choices = [
        self::PROVISION => 'Provision',
        self::ACTIVE    => 'Active',
        self::INACTIVE  => 'Inactive'
    ];
}