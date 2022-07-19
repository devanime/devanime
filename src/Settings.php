<?php

namespace DevAnime;
/**
 * Class Settings
 * @package DevAnime
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 * @property string $ver                  Plugin version number
 * @property string $base_dir             Base plugin directory
 * @property string $base_url             Base plugin url
 * @property string $slug                 Plugin slug
 */
class Settings {
    private $_settings;

    public function __construct($args, $file) {
        $holder = explode(DIRECTORY_SEPARATOR, plugin_basename($file));
        $slug = array_shift($holder);
        $defaults = [
            'ver'      => '1.0',
            'base_dir' => plugin_dir_path($file),
            'base_url' => plugin_dir_url($file),
            'slug'     => $slug,
        ];
        $this->_settings = wp_parse_args($args, $defaults);
    }

    public function __get($name) {
        if (array_key_exists($name, $this->_settings)) {
            return $this->_settings[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return null;
    }

}