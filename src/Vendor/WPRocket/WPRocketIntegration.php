<?php
/**
 * Class WPRocketIntegration
 * @package DevAnime\Vendor\WPRocket
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\Vendor\WPRocket;

class WPRocketIntegration
{

    public function __construct()
    {
        add_filter('before_rocket_htaccess_rules', [$this, 'forceTrailingSlash']);
    }

    public function forceTrailingSlash($marker)
    {

        $redirection = '# Force trailing slash' . PHP_EOL;
        $redirection .= 'RewriteEngine On' . PHP_EOL;
        $redirection .= 'RewriteCond %{REQUEST_FILENAME} !-f' . PHP_EOL;
        $redirection .= 'RewriteCond %{REQUEST_METHOD} GET' . PHP_EOL;
        $redirection .= 'RewriteCond %{REQUEST_URI} !(.*)/$' . PHP_EOL;
        $redirection .= 'RewriteCond %{REQUEST_URI} !^/wp-(content|admin|includes)' . PHP_EOL;
        $redirection .= 'RewriteCond %{REQUEST_FILENAME} !\.(gif|jpg|png|jpeg|css|xml|txt|js|php|scss|webp|mp3|avi|wav|mp4|mov)$ [NC]' . PHP_EOL;
        $redirection .= 'RewriteRule ^(.*)$ http' . (is_ssl() ? 's' : '') . '://%{HTTP_HOST}/$1/ [L,R=301]' . PHP_EOL . PHP_EOL;

        // Prepend redirection rules to WP Rocket block.
        $marker = $redirection . $marker;

        return $marker;
    }
}