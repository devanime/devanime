<?php
/*
Plugin Name: DevAnime
Description: A plugin framework for easily creating re-usable modules across projects.
Version: 1.0
License: GPL-2.0+
*/

use DevAnime\Controller;
use DevAnime\Register;

// Exit if accessed directly
defined('ABSPATH') || exit;
if (! defined('USE_COMPOSER_AUTOLOADER') || ! USE_COMPOSER_AUTOLOADER) {
    require __DIR__ . '/vendor/autoload.php';
}

class DevAnime {

    const LIVE_CONTROLLERS = [
        Controller\RegisterConfigController::class,
        Controller\PostController::class,
        Controller\AcfController::class,
        Controller\PermalinksController::class,
        Controller\AutomaticUpdatesController::class,
        Controller\FactoryController::class,
    ];

    public function __construct() {
        add_action('devanime/register', [$this, 'register'], 10, 3);
        add_action('plugins_loaded', function () {
            do_action('devanime/init');
        });

        foreach(static::LIVE_CONTROLLERS as $Controller) {
            new $Controller();
        }

//        new Controller\RegisterConfigController();
//        new Controller\PostController();
//        new Controller\AcfController();
//        new Controller\PermalinksController();
//        new Controller\AutomaticUpdatesController();
//        new Controller\FactoryController();

        //

//        new ConfigLoader();
//        new PermalinksManager();
//        new AfterSavePostHandler();
//        new AutomaticUpdaterHandler();
//        new FactoryLoader();

        // TODO lost complete support for these atm
//        new VendorIntegrationController();
//        new CacheController();
    }

    public function register($data, $key, $type) {
        switch ($type) {
            case 'options':
                new Register\RegisterOption($data);
                break;
            case 'custom_post_types':
                new Register\RegisterPostType($key, $data);
                break;
            case 'taxonomies':
                new Register\RegisterTaxonomy($key, $data);
                break;
        }
    }
}

new DevAnime();

