<?php

namespace DevAnime\Support;

/**
 * Class NetworkAdminScreenSwitcher
 * @package DevAnime\Support
 */
class NetworkAdminScreenSwitcher
{
    public function __construct()
    {
        add_action('admin_init', function () {
            add_action('admin_bar_menu', [$this, 'sitesAdminBar'], 30);
        }, 99);
    }

    public function sitesAdminBar(\WP_Admin_Bar $wp_admin_bar)
    {
        // Don't show for logged out users or single site mode.
        if (!is_user_logged_in() || !is_multisite() || !is_admin())
            return;

        // Show only when the user has at least one site, or they're a super admin.
        if (count($wp_admin_bar->user->blogs) < 1 && !current_user_can('manage_network')) {
            return;
        }

        foreach ((array)$wp_admin_bar->user->blogs as $blog) {
            switch_to_blog($blog->userblog_id);
            $menu_id = 'blog-' . $blog->userblog_id;
            if (is_main_site($blog->userblog_id)) {
                $wp_admin_bar->remove_menu($menu_id . '-v');
            } else {
                $wp_admin_bar->add_menu(array(
                    'parent' => $menu_id,
                    'id' => $menu_id . '-a',
                    'title' => __('Current Admin Screen'),
                    'href' => admin_url(explode('wp-admin/', $_SERVER['REQUEST_URI'])[1]),
                ));
            }
            $wp_admin_bar->remove_menu($menu_id . '-c');
            $wp_admin_bar->remove_menu($menu_id . '-n');

            restore_current_blog();
        }
    }
}