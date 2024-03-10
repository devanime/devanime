<?php

namespace DevAnime\Controller;

/**
 * Class RegisterConfigController
 * @package Devanime\Register
 */
class RegisterConfigController {
    protected $config_files = [];
    protected $data;

    function __construct() {
        add_action('init', [$this, 'initConfig'], 5);
    }

    public function initConfig() {
        $this->config_files = array_values(array_filter(apply_filters('devanime/register_config', []), 'file_exists'));
        if (!empty($this->config_files)) {
            $this->data = $this->applyFilters(
                $this->compileFileData([
                    'options'           => [],
                    'custom_post_types' => [],
                    'taxonomies'        => []
                ])
            );
        }
    }

    protected function compileFileData($data) {
        foreach ($this->config_files as $file) {
            $import = json_decode(file_get_contents($file), true);
            foreach ($data as $key => $value) {
                if (isset($import[$key])) {
                    $data[$key] = array_merge($data[$key], $import[$key]);
                }
            }
        }

        return $data;
    }

    protected function applyFilters($data) {
        foreach ($data as $type => $list) {
            foreach ($list as $key => $value) {
                $list[$key] = apply_filters('devanime/config', $value, $key, $type);
                do_action('devanime/register', $list[$key], $key, $type);
            }
            $data[$type] = array_filter($list);
        }

        return $data;
    }
}