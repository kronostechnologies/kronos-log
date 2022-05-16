<?php

namespace Kronos\Log;

/**
 * Represent a base class enumeration. Any new Enum should extends from this class.
 * Enum value must be declared as constants like  "const name = 'value';".
 *
 * This class was based on Brian Cline answer in http://stackoverflow.com/questions/254514/php-and-enumerations
 */
abstract class  Enumeration
{
    private static $cache = null;

    private static function initialiseCache()
    {
        if (self::$cache === null) {
            self::$cache = array();
        }
    }

    private static function getConstants()
    {
        self::initialiseCache();

        $classname = get_called_class();
        if (!array_key_exists($classname, self::$cache)) {
            $r = new \ReflectionClass($classname);
            self::$cache[$classname] = $r->getConstants();
        }

        return self::$cache[$classname];
    }

    final public static function isValidName($name)
    {
        return array_key_exists($name, self::getConstants());
    }

    final public static function isValidValue($value)
    {
        return in_array($value, self::getConstants());
    }
}
