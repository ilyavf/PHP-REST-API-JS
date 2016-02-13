<?php

/*
 * Enums basic Class
 */
abstract class BasicEnum {
    private static $constCacheArray = NULL;

    private static function getConstants() {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true) {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}

/*
 * Enums for two factor
 */
abstract class TwoFactor {
    const None = 0;
    const Email = 1;
    const SMS = 2;
    const Device = 3;
}

/*
 * Class that all controllers classes extends from
 */
class BaseAPIController {
    public $authPassed;

    public function __construct($authPassed) {
        $this->authPassed = $authPassed;
    }

    public function checkAuth() {
        if (!$this->authPassed) echo json_encode(StatusReturn::E401('401 Not Authorized!'));
        return $this->authPassed;
    }
}