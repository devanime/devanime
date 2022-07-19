<?php
/**
 * Class VendorIntegrationController
 * @package Backstage\Vendor
 * @author  Jeremy Strom <jstrom@situationinteractive.com>
 * @version 1.0
 */

namespace Backstage\Vendor;

use Backstage\Vendor\AdvancedCustomFields\ACFIntegration;
use Backstage\Vendor\GravityForms\GFIntegration;
use Backstage\Vendor\Imagify\ImagifyIntegration;
use Backstage\Vendor\WPRocket\WPRocketIntegration;
use Backstage\Vendor\Yoast\YoastSEOIntegration;

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
