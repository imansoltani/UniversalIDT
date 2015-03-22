<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class RequestsStatusEnumType extends AbstractEnumType
{
    const PENDING = "P";
    const DONE = "D";

    protected static $choices = [
        self::PENDING => 'Pending',
        self::DONE    => 'Done'
    ];
}