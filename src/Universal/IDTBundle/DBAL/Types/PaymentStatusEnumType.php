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
        self::STATUS_ACCEPTED => 'accepted',
        self::STATUS_CANCELED => 'canceled',
        self::STATUS_DECLINED => 'declined',
        self::STATUS_PENDING  => 'pending',
        self::STATUS_UNCERTAIN => 'uncertain',
        self::STATUS_UNKNOWN  => 'unknown'
    ];
}