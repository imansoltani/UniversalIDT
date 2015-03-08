<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class PaymentStatusEnumType extends AbstractEnumType
{
    const STATUS_ACCEPTED = 'A';
    const STATUS_CANCELED = 'C';
    const STATUS_DECLINED = 'D';
    const STATUS_PENDING  = 'P';
    const STATUS_UNCERTAIN = 'T';
    const STATUS_UNKNOWN  = 'U';

    protected static $choices = [
        self::STATUS_ACCEPTED => 'Accepted',
        self::STATUS_CANCELED => 'Canceled',
        self::STATUS_DECLINED => 'Declined',
        self::STATUS_PENDING  => 'Pending',
        self::STATUS_UNCERTAIN => 'Uncertain',
        self::STATUS_UNKNOWN  => 'Unknown'
    ];
}