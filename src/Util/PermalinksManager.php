<?php
/**
 * Class Permalinks_Manager
 * @package DevAnime\Util
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\Util;

class PermalinksManager {
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
