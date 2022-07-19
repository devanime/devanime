<?php
/**
 * Class ImagifyIntegration
 * @author  Jeremy Strom <jstrom@situationinteractive.com>
 * @version 1.0
 */

namespace Backstage\Vendor\Imagify;

class ImagifyIntegration
{

    public function __construct()
    {
        add_filter('imagify_site_root', [$this, 'setSiteRoot'], 10001);
        add_action('plugins_loaded', [$this, 'disablePluginOffProduction'], 5);
    }

    public function setSiteRoot($root_path)
    {
        $upload_basedir = imagify_get_filesystem()->get_upload_basedir(true);

        if (strpos($upload_basedir, '/wp-content/') === false) {
            return $root_path;
        }

        $upload_basedir = explode('/wp-content/', $upload_basedir);
        $upload_basedir = reset($upload_basedir);

        return trailingslashit($upload_basedir);
    }

    public function disablePluginOffProduction()
    {
        if (! (
            (defined('ENVIRONMENT') && ENVIRONMENT === 'production') 
            || (defined('IMAGIFY_TEST') && IMAGIFY_TEST)
        )) {
            remove_action('plugins_loaded', '_imagify_init');
        }
    }
}