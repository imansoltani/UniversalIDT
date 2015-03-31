<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class RequestStatusEnumType extends AbstractEnumType
{
    const REGISTERED = "R";
    const PENDING_FOR_CREATION = "PC";
    const PENDING_FOR_RECHARGE = "PR";
    const FAILED_AT_CREATION = "FC";
    const FAILED_AT_RECHARGE = "FR";
    const SUCCEED = "S";

    protected static $choices = [
        self::REGISTERED => 'Registered',
        self::PENDING_FOR_CREATION => 'Pending for creation',
        self::PENDING_FOR_RECHARGE => 'Pending for recharge',
        self::FAILED_AT_CREATION   => 'Failed at creation',
        self::FAILED_AT_RECHARGE   => 'Failed at recharge',
        self::SUCCEED  => 'Succeed'
    ];
}