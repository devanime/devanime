<?php

namespace DevAnime\Controller;

/**
 * class Acf
 * @package DevAnime\Controller;
 */
class AcfController
{
    public function __construct()
    {
        add_action('acf/include_field_types', function () {
            if (did_action('after_setup_theme')) {
                return;
            }
            $acf_json = acf()->json;
            remove_action('acf/include_fields', [$acf_json, 'include_json_folders']);
            add_action('init', [$acf_json, 'include_json_folders'], 1);
        });
    }
}
