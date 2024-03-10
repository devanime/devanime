<?php


namespace DevAnime\Support;


trait Singleton
{
    private static $instances = [];

    final private function __construct() {}
    final private function __clone() {}
    final private function __sleep() {}
    final private function __wakeup() {}

    final public static function getInstance()
    {
        if (!isset(self::$instances[static::class])) {
            self::$instances[static::class] = new static;
        }
        return self::$instances[static::class];
    }
}