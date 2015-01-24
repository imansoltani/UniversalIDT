<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class RequestStatusEnumType extends AbstractEnumType
{
    const PENDING = "P";
    const FAILED = "F";
    const SUCCEED = "S";

    protected static $choices = [
        self::PENDING => 'Pending',
        self::FAILED    => 'Failed',
        self::SUCCEED  => 'Succeed'
    ];
}