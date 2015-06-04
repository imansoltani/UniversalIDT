<?php
namespace Universal\IDTBundle\DBAL\Types;


use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

class GenderEnumType extends AbstractEnumType
{
    const MALE = "M";
    const FEMALE = "F";

    protected static $choices = [
        self::MALE => 'Male',
        self::FEMALE    => 'Female',
    ];

    public static function getChoicesWithPrefix($prefix, $lower = false)
    {
        return array_map(function($value) use ($prefix,$lower) { return $prefix . ($lower?strtolower($value):$value); }, static::$choices);
    }
}