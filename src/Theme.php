<?php

namespace DevAnime;

use DevAnime\Html\LoadStack;
use DevAnime\Util\TemplateWrapper;
use DevAnime\Util\UploadSVG;

/**
 * Class Theme
 * @package DevAnime
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */
class Theme
{
    const API_PREFIX = 'api';
    const INCLUDE_ADMIN_ASSETS = false;
    const INCLUDE_ADMIN_EDITOR = true;
    const REMOVE_DEFAULT_POST_TYPE = false;
    const REMOVE_COMMENTS = true;
    const RENAME_DEFAULT_POST_TYPE = false;

    const NAV_MENUS = [
        'primary_navigation' => 'Primary/Header Navigation',
        'footer_navigation'  => 'Footer Navigation'
    ];

    const DEFAULT_THEME_SUPPORT = [
        'title-tag',
        'post-thumbnails'
    ];
    const SOIL_THEME_SUPPORT = [
        'clean-up',
        'disable-asset-versioning',
        'disable-trackbacks',
        'js-to-footer',
        'nav-walker',
        'nice-search',
        'relative-urls'
    ];

    const PLATFORM_THEME_SUPPORT = [
        'gilgamesh/nav-menu'
    ];

    const EXTENSIONS = [];

    const POST_TYPE_IMAGE_DIMENSIONS = [];

    const POST_TYPE_IMAGE_DIMENSIONS_MESSAGE = '<p>Preferred Dimensions: %d x %d pixels</p>';

    const DEFAULT_CONFIG_PATHS = [
        'config_files' => 'config',
        'acf_paths' => 'acf-json'
    ];

    /**
     * Theme constructor.
     * @param string $theme_dir - deprecated
     */
    public function __construct($theme_dir = '')
    {
        new LoadStack();
        new UploadSVG();
        TemplateWrapper::init();
        add_action('after_setup_theme', [$this, 'setup']);
        add_action('after_setup_theme', [$this, 'config'], 100);
        add_action('wp_enqueue_scripts', [$this, 'assets'], 100);
        add_filter('show_admin_bar', '__return_false');
        add_action('wp_body_open', [$this, 'afterOpeningBody']);
        add_filter('admin_post_thumbnail_html', [$this, 'adminPostThumbnailHtml'], 10, 2);
        add_filter('rest_url_prefix', function () { return static::API_PREFIX; });
        add_filter('wp_targeted_link_rel', [$this, 'removeNoReferrerFromLinks']);
        if (static::INCLUDE_ADMIN_ASSETS) {
            add_action('admin_enqueue_scripts', [$this, 'adminAssets'], 100);
        }
        if (static::REMOVE_DEFAULT_POST_TYPE) {
            add_action('admin_menu', [$this, 'removeDefaultPostType']);
        }
        if (static::RENAME_DEFAULT_POST_TYPE) {
            add_filter('post_type_labels_post', [$this, 'renameDefaultPostType']);
        }
        foreach (static::EXTENSIONS as $extension) {
            if (class_exists($extension)) {
                $extension = new $extension;
                if (method_exists($extension, 'register')) {
                    $extension->register();
                }
            }
        }
        add_filter('user_can_richedit', function ($wp_rich_edit) {
            if (! $wp_rich_edit
                && (get_user_option('rich_editing') == 'true' || ! is_user_logged_in())
                && isset($_SERVER['HTTP_USER_AGENT'])
                && stripos($_SERVER['HTTP_USER_AGENT'], 'amazon cloudfront') !== false
            ) {
                return true;
            }

            return $wp_rich_edit;
        });
    }

    /**
     * Theme setup
     */
    public function setup()
    {
        if (! empty(static::SOIL_THEME_SUPPORT)) {
            add_theme_support('soil', static::SOIL_THEME_SUPPORT);
        }
        if (static::INCLUDE_ADMIN_EDITOR) {
            add_editor_style(Util::getAssetPath('styles/admin-editor.css'));
        }
        register_nav_menus(static::NAV_MENUS);
        array_map(
            'add_theme_support',
            array_merge(static::DEFAULT_THEME_SUPPORT, static::PLATFORM_THEME_SUPPORT)
        );
        add_action('after_setup_theme', function() {
            remove_filter('body_class', 'Roots\Soil\CleanUp\body_class');
        }, 200);
        add_filter('body_class', [$this, 'cleanupBodyClass']);
    }

    public function config()
    {
        $config_files = [];
        foreach ($this->getConfigPaths() as $type => $path) {
            $should_glob = substr($type, -6) == '_files';
            $parent_path = TEMPLATEPATH . '/' . $path;
            $files = $should_glob ? glob($parent_path . '/*.json') : [$parent_path];
            if (is_child_theme()) {
                $child_path = STYLESHEETPATH . '/' . $path;
                $files = array_merge($files, $should_glob ? glob($child_path . '/*.json') : [$child_path]);
            }
            $config_files[$type] = $files;
        }
        new Config($config_files);
    }

    /**
     * Modified from Roots\Soil\CleanUp\body_class()
     *
     * @param array $classes
     * @return array
     */
    public function cleanupBodyClass($classes)
    {
        $home_id_class = 'page-id-' . get_option('page_on_front');
        $remove_classes = [
            'page-template-default',
            $home_id_class
        ];
        $classes = array_diff($classes, $remove_classes);
        return $classes;
    }

    /**
     * Theme assets
     */
    public function assets()
    {
        wp_enqueue_style('theme/css', Util::getAssetPath('styles/main.css'), false, null);
        if (!static::REMOVE_COMMENTS && is_single() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
        wp_enqueue_script('theme/js', Util::getAssetPath('scripts/main.js'), ['jquery'], null, true);
        $js_vars = apply_filters('global_js_vars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'api_url' => trailingslashit(home_url(rest_get_url_prefix()))
        ]);
        wp_localize_script('theme/js', 'sit', $js_vars);
    }

    public function adminAssets()
    {
        wp_enqueue_style('theme/admin/css', Util::getAssetPath('styles/admin.css'), false, null);
        wp_enqueue_script('theme/admin/js', Util::getAssetPath('scripts/admin.js'));
    }

    public function removeDefaultPostType()
    {
        remove_menu_page('edit.php');
        if (static::REMOVE_COMMENTS) {
            remove_menu_page('edit-comments.php');
        }
    }

    public function renameDefaultPostType($labels)
    {
        return (object) array_map(function ($l) {
            return str_ireplace('Post', static::RENAME_DEFAULT_POST_TYPE, $l);
        }, (array) $labels);
    }

    public function afterOpeningBody()
    {
        $sprite = get_theme_file_path('dist/images/sprite.svg');
        if (file_exists($sprite)) {
            echo file_get_contents($sprite);
        }
    }

    public function adminPostThumbnailHtml($content, $post_id)
    {
        $post = get_post($post_id);
        if (isset(static::POST_TYPE_IMAGE_DIMENSIONS[$post->post_type])) {
            $dimensions = static::POST_TYPE_IMAGE_DIMENSIONS[$post->post_type];
            $content = vsprintf(static::POST_TYPE_IMAGE_DIMENSIONS_MESSAGE, $dimensions) . $content;
        }
        return $content;
    }

    protected function getConfigPaths()
    {
        return static::DEFAULT_CONFIG_PATHS;
    }

    public function removeNoReferrerFromLinks($rel_values) {
        return preg_replace('/noreferrer\s*/i', '', $rel_values);
    }
}
