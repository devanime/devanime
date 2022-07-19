<?php
/*
Plugin Name: Backstage
Description: A plugin framework for easily creating re-usable modules across projects.
Version: 9999
License: GPL-2.0+
*/

use Backstage\ConfigLoader;
use Backstage\Vendor\VendorIntegrationController;
use Backstage\Cache\CacheController;
use Backstage\FactoryLoader;
use Backstage\PostTypes\PostTypeHandler;
use Backstage\Options\RegisterOption;
use Backstage\Taxonomies\TaxonomyHandler;
use Backstage\Util\PermalinksManager;
use Backstage\Util\AfterSavePostHandler;
use Backstage\Util\AutomaticUpdaterHandler;

// Exit if accessed directly
defined('ABSPATH') || exit;
if (! defined('USE_COMPOSER_AUTOLOADER') || ! USE_COMPOSER_AUTOLOADER) {
    require __DIR__ . '/vendor/autoload.php';
}

class Backstage {


    public function __construct() {
        add_action('backstage/register', [$this, 'register'], 10, 3);
        add_action('plugins_loaded', function () {
            do_action('backstage/init');
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

new Backstage();

