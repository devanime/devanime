<?php

namespace DevAnime\Controller;

/**
 * class CacheController
 * @package DevAnime\Controller
 *
 * @TODO unused
 */
class CacheController
{
    const CACHE_CLEAR_TRANSIENT = 'devanime_cache_clear';
    const CACHE_LOCK_TRANSIENT  = 'devanime_cache_lock';
    const CACHE_PROPAGATION_SCRIPT_PATH = '/deploy/scripts/post_deploy.sh';
    const DEFAULT_CACHE_CLEAR_TTL = 300;

    private $cache_file;
    private $cache_clear_ttl;

    protected $clear_cache_hooks = [
        'wpsdb_migration_complete',
        'save_post',
        'transition_post_status',
        'added_post_meta',
        'deleted_post_meta',
        'acf/save_post'
    ];

    public function __construct()
    {
        add_action('init', [$this, 'init']);
        add_action('before_rocket_clean_domain', 'wp_cache_flush');
        add_action('after_rocket_clean_domain', [$this, 'propagate']);
        add_filter('rocket_cache_reject_wp_rest_api', '__return_false');
        add_filter('do_rocket_generate_caching_files', [$this, 'shouldCacheFiles']);
        add_filter('cloudflare_purge_everything_actions', function ($actions) {
            return array_merge($actions ?: [], ['after_rocket_clean_domain', 'devanime/trigger_cloudflare_purge']);
        });
    }

    public function init()
    {
        $uploads = wp_upload_dir();
        $this->cache_file = $uploads['basedir'] . '/.clear-cache';
        foreach ((array) apply_filters('devanime/cache/clear-hooks', $this->clear_cache_hooks) as $hook) {
            add_action($hook, [__CLASS__, 'flagCacheClear']);
        }
        $this->cache_clear_ttl = apply_filters('devanime/cache/clear-ttl', static::DEFAULT_CACHE_CLEAR_TTL);
        $this->maybeFlushCache();
    }

    public function shouldCacheFiles($flag)
    {
        return
            defined('ENVIRONMENT') &&
            ENVIRONMENT == 'local' &&
            !(defined('LOCAL_CACHE') && LOCAL_CACHE) ? false : $flag;

    }

    public function clearAllCache()
    {
        do_action('devanime/before_cache_clear');
        /**
         * Slave servers can't clear cache directly
         */
        if ($this->isSlaveServer()) {
            $this->log('Skipping slave server');
            return;
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        } else {
            wp_cache_flush();
            do_action('devanime/trigger_cloudflare_purge');
        }
        $this->log('Cache cleared');
        do_action('devanime/after_cache_clear');
    }

    /**
     * Execute cache-clearing cluster propagation
     */
    public function propagate()
    {
        if (! function_exists('exec')) {
            return;
        }
        if ($script = $this->getPropagationScript()) {
            $this->log('Cluster propagation script executed');
            exec($script);
        }
        $this->clearWebpTmp();
    }

    public function maybeFlushCache()
    {
        $flush = false;
        if (file_exists($this->cache_file)) {
            wp_delete_file($this->cache_file);
            $flush = true;
        }
        if (
            get_transient(static::CACHE_CLEAR_TRANSIENT) &&
            !get_transient(static::CACHE_LOCK_TRANSIENT)
        ) {
            $flush = true;
            delete_transient(static::CACHE_CLEAR_TRANSIENT);
            set_transient(static::CACHE_LOCK_TRANSIENT, true, $this->cache_clear_ttl);
            $this->log('Transient reset');
        }
        if ($flush) {
            $this->clearAllCache();
        }
    }

    public static function flagCacheClear()
    {
        set_transient(static::CACHE_CLEAR_TRANSIENT, true);
    }

    protected function isSlaveServer()
    {
        return !empty($_SERVER['CLUSTER_ROLE']) && false !== stripos($_SERVER['CLUSTER_ROLE'], 'slave');
    }

    protected function getPropagationScript()
    {
        $below_root = dirname($_SERVER['DOCUMENT_ROOT']);
        $script = $below_root . static::CACHE_PROPAGATION_SCRIPT_PATH;
        return file_exists($script) ? $script : false;
    }

    protected function log($message)
    {
        if (defined('WP_CACHE_DEBUG') && WP_CACHE_DEBUG) {
            error_log('Cache Debug: ' . $message);
        }
    }

    protected function clearWebpTmp()
    {
        $uploads = dirname($this->cache_file);
        exec('find ' . $uploads . ' -type f -name "*.webp.tmp" -exec rm -rf {} \;');
    }
}