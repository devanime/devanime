<?php
/**
 * Class DevAnime_Base
 * @package DevAnime
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime;

class DevAnimeBase {
    protected static $_settings = [];
    protected $_config;
    protected static $_default_settings = [];
    protected static $_file = null;

    /**
     * @return Settings
     */
    public static function settings() {
        $class = get_called_class();
        if (empty(static::$_settings[$class])) {
            $file = static::$_file;
            if (empty(static::$_file)) {
                $reflector = new \ReflectionClass($class);
                $file = $reflector->getFileName();
            }
            static::$_settings[$class] = new Settings(static::$_default_settings, $file);
        }

        return static::$_settings[$class];
    }
}