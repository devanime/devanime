<?php

namespace DevAnime\Controller;

/**
 * Class PermalinksController
 * @package DevAnime\Controller
 */
class PermalinksController {
    private static $rewritable = [];
    private static $version;
    private static $option_name = 'devanime_version';

    public function __construct() {
        if (is_admin()) {
            add_action('devanime/register', [$this, 'register']);
            add_action('admin_init', [$this, 'maybeFlushRewrite']);
        }
    }

    public function register($data) {
        static::$rewritable[] = $data;
    }

    public static function version() {
        if (empty(static::$version)) {
            static::$version = get_option(static::$option_name);
        }

        return static::$version;
    }

    public function maybeFlushRewrite() {
        $hash = md5(serialize(static::$rewritable));
        if (static::version() !== $hash) {
            flush_rewrite_rules();
            update_option(static::$option_name, $hash, false);
        }
    }
}
