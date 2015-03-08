<?php
namespace Universal\IDTBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class PaymentMethodEnumType extends AbstractEnumType
{
    const OGONE = "O";
    const SOFORT = "S";

    protected static $choices = [
        self::OGONE => 'Ogone',
        self::SOFORT => 'Sofort'
    ];
}