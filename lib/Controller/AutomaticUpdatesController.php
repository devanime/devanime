<?php

namespace DevAnime\Controller;

/**
 * class AutomaticUpdatesController
 * @package DevAnime\Controller
 */
class AutomaticUpdatesController
{
    public function __construct()
    {
        if ($this->isEnabled()) {
            add_filter('automatic_updater_disabled', '__return_false');
            add_filter('auto_update_plugin', '__return_false');
            add_filter('auto_update_theme', '__return_false');
            add_filter('plugins_auto_update_enabled', '__return_false');
            add_filter('themes_auto_update_enabled', '__return_false');
            add_filter('allow_minor_auto_core_updates', '__return_true');
            add_filter('file_mod_allowed', function($allowed, $context) {
                return $context == 'automatic_updater' ?: $allowed;
            }, 10, 2);
        }
    }

    protected function isEnabled()
    {
        return
            defined('AUTOMATIC_UPDATER_DISABLED') && AUTOMATIC_UPDATER_DISABLED &&
            defined('DEVANIME_AUTOMATIC_UPDATER_HANDLER') && DEVANIME_AUTOMATIC_UPDATER_HANDLER;
    }
}
