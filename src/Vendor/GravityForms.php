<?php

namespace DevAnime\Vendor;
use DevAnime\Vendor\GravityForms\GFIntegration;

/**
 * @deprecated
 */
class GravityForms
{
    const CONFIRM_TRACKING_FIELD = GFIntegration::CONFIRM_TRACKING_FIELD;
    const CONFIRM_TRACKING_LABEL = GFIntegration::CONFIRM_TRACKING_LABEL;

    public function __construct()
    {
        trigger_error('The GravityForms class hooks are now instantiated by default in DevAnime.', E_USER_DEPRECATED);
    }
}