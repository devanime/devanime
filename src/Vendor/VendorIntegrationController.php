<?php
/**
 * Class VendorIntegrationController
 * @package DevAnime\Vendor
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\Vendor;

use DevAnime\Vendor\AdvancedCustomFields\ACFIntegration;
use DevAnime\Vendor\GravityForms\GFIntegration;
use DevAnime\Vendor\Imagify\ImagifyIntegration;
use DevAnime\Vendor\WPRocket\WPRocketIntegration;
use DevAnime\Vendor\Yoast\YoastSEOIntegration;

class VendorIntegrationController
{
    public function __construct()
    {
        new ACFIntegration();
        new GFIntegration();
        new WPRocketIntegration();
        new ImagifyIntegration();
        new YoastSEOIntegration();
        
        add_filter('customize_loaded_components', function() {
            return ['widgets'];
        });
    }
}
