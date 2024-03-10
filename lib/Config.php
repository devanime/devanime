<?php

namespace DevAnime;

/**
 * Class Config
 * @package DevAnime
 */
class Config
{
    protected $_config;

    /**
     * Config constructor.
     *
     * @param array|string $args
     */

    protected static $default_registration_types = [
        'config_files' => 'devanime/register_config',
        'acf_paths' => 'acf/settings/load_json',
        'post_type_models' => 'devanime/register_post_type_models',
        'taxonomy_models' => 'devanime/register_taxonomy_models'
    ];

    public function __construct($args) {
        if (is_string($args)) {
            $args = ['config_files' => [$args]];
        }
        $defaults = [
            'config_files' => [],
            'acf_paths'    => [],
            'post_type_models' => [],
            'taxonomy_models' => []
        ];
        $this->_config = wp_parse_args($args, $defaults);
        $registration_types = apply_filters('devanime/config_registration_types', static::$default_registration_types);
        foreach ($registration_types as $key => $filter_name) {
            if (empty($this->_config[$key])) {
                continue;
            }
            if (!is_array($this->_config[$key])) {
                $this->_config[$key] = [$this->_config[$key]];
            }
            add_filter($filter_name, function(array $configs = []) use ($key) {
                return array_merge($configs, $this->_config[$key]);
            });
        }
    }
}