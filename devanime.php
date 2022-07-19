<?php
/*
Plugin Name: DevAnime
Description: A plugin framework for easily creating re-usable modules across projects.
Version: 9999
License: GPL-2.0+
*/

use DevAnime\ConfigLoader;
use DevAnime\Vendor\VendorIntegrationController;
use DevAnime\Cache\CacheController;
use DevAnime\FactoryLoader;
use DevAnime\PostTypes\PostTypeHandler;
use DevAnime\Options\RegisterOption;
use DevAnime\Taxonomies\TaxonomyHandler;
use DevAnime\Util\PermalinksManager;
use DevAnime\Util\AfterSavePostHandler;
use DevAnime\Util\AutomaticUpdaterHandler;

// Exit if accessed directly
defined('ABSPATH') || exit;
if (! defined('USE_COMPOSER_AUTOLOADER') || ! USE_COMPOSER_AUTOLOADER) {
    require __DIR__ . '/vendor/autoload.php';
}

class DevAnime {


    public function __construct() {
        add_action('devanime/register', [$this, 'register'], 10, 3);
        add_action('plugins_loaded', function () {
            do_action('devanime/init');
        });
        new ConfigLoader();
        new VendorIntegrationController();
        new CacheController();
        new FactoryLoader();
        new PermalinksManager();
        new AfterSavePostHandler();
        new AutomaticUpdaterHandler();
    }

    public function register($data, $key, $type) {
        switch ($type) {
            case 'options':
                new RegisterOption($data);
                break;
            case 'custom_post_types':
                new PostTypeHandler($key, $data);
                break;
            case 'taxonomies':
                new TaxonomyHandler($key, $data);
                break;
        }
    }
}

new DevAnime();

