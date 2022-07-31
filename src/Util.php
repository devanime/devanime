<?php
/**
 * Class Util
 * @package DevAnime
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime;

use DevAnime\Cache\CacheController;
use DevAnime\Support\DateTime;
use DevAnime\Util\JsonManifest;

class Util
{
    /**
     * Will pluralize most words, but edge cases shouldn't rely on this for proper pluralization.
     *
     * @param string $word
     *
     * @return string
     */
    public static function pluralize($word)
    {
        $exploded = str_split(trim($word));
        $last = array_pop($exploded);
        switch ($last) {
            case 'y':
                $exploded[] = 'ies';
                break;
            case 's';
                $exploded[] = $last . 'es';
                break;
            default:
                $exploded[] = $last . 's';
                break;
        }

        return implode($exploded);
    }

    /**
     * Will singularize most words, but edge cases shouldn't rely on this for proper singularization.
     *
     * @param string $word
     *
     * @return string
     */
    public static function singularize($word)
    {
        $length = strlen($word);
        if ($length > 3 && $length - 3 === strrpos($word, 'ies', -3)) {
            return substr($word, 0, -3) . 'y';
        }
        if ($length > 2 && $length - 2 === strrpos($word, 'es', -2)) {
            return substr($word, 0, -2);
        }
        if ($length > 1 && $length - 1 === strrpos($word, 's', -1)) {
            return substr($word, 0, -1);
        }
        return $word;
    }

    /**
     * Return a valid DateTime object built from a date string and optional time string.
     * Tests time string for valid format.
     *
     * @param string $date_str
     * @param string|null $time_str
     *
     * @return DateTime|false
     */
    public static function validDatetime($date_str, $time_str = null)
    {
        if (empty($date_str)) {
            return false;
        }
        $test_time = strtotime($time_str);
        $parse_str = empty($test_time) ? $date_str : $date_str . ' ' . $time_str;

        return new DateTime($parse_str);
    }

    public static function sanitizeKey($label)
    {
        return str_replace('-', '_', sanitize_title($label));
    }

    public static function isUrlExternal($url)
    {
        $normalize_host = function ($host) {
            return str_replace('www.', '', strtolower($host));
        };
        // Handle fragments like "#tickets"
        if (empty($url) || strpos($url, '#') === 0) {
            return false;
        }
        $components = parse_url($url);
        // Handle relative urls like "/about"
        if (
            empty($components['host']) &&
            !empty($components['path']) &&
            strpos($components['path'], '/') === 0
        ) {
            return false;
        }
        $home = parse_url(home_url());
        // Compare host
        if (
            isset($components['host']) &&
            isset($home['host']) &&
            $normalize_host($components['host']) === $normalize_host($home['host'])
        ) {
            return false;
        }

        return true;
    }

    /**
     * Duplicate of wp filter 'the_content'.
     * Useful alternative because some plugins use 'the_content' filter to append additional content.
     *
     * @param string $str
     *
     * @return string
     */
    public static function filterContent($str)
    {
        return wpautop(wptexturize($str));
    }

    /**
     * Truncate string by character length rounded to the nearest word.
     *
     * @param string $text
     * @param int $length
     * @param string $append
     *
     * @return string Truncated text
     */
    public static function truncateText($text, $length = 48, $append = "...")
    {
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $text = self::cutUsingLast(" ", $text, "left", false);

            return $text . $append;
        } else {
            return $text;
        }
    }

    /**
     * @param        $character
     * @param        $string
     * @param string $side
     * @param bool $keep_character
     *
     * @return bool|string
     */
    public static function cutUsingLast($character, $string, $side = 'left', $keep_character = true)
    {
        $offset = ($keep_character ? 1 : 0);
        $whole_length = strlen($string);
        $right_length = (strlen(strrchr($string, $character)) - 1);
        $left_length = ($whole_length - $right_length - 1);
        switch ($side) {
            case 'left':
                $piece = substr($string, 0, ($left_length + $offset));
                break;
            case 'right':
                $start = (0 - ($right_length + $offset));
                $piece = substr($string, $start);
                break;
            default:
                $piece = false;
                break;
        }

        return ($piece);
    }

    public static function getFirstParagraph($html)
    {
        $start = strpos($html, '<p>');
        $end = strpos($html, '</p>', $start);
        $paragraph = substr($html, $start, $end - $start + 4);

        return $paragraph;
    }

    /**
     * @param $string
     * @param $count
     *
     * @return string Last $count number of words in $string
     */
    public static function getLastWords($string, $count)
    {
        $arr = explode(' ', $string);
        $frag = array_slice($arr, -$count, $count);
        $ret = implode(' ', $frag);
        $ret = trim(ucwords($ret));

        return $ret;
    }

    /**
     * Generate Lorem Ipsum placeholder content.
     * @link http://loripsum.net
     *
     * @param string $param_str
     *
     * @return string
     */
    public static function placeholderText($param_str = "5 long decorate link")
    {
        $params = explode(' ', $param_str);
        $base_url = "http://loripsum.net/api/";
        $body = wp_remote_retrieve_body(wp_remote_get($base_url . implode('/', $params)));

        return $body;
    }

    public static function placeholderImage($width, $height)
    {
        return '<img src="http://placekitten.com/' . $width . '/' . $height . '" width="' . $width . '" height="' . $height . '" />';
    }

    /**
     * Insert array contents into html list.
     *
     * @param $content_arr array HTML strings to convert to list elements.
     * @param $args        array Settings for list_type (ul/ol), list_class, item_class, attr
     *
     * @return string HTML ul/ol list.
     */
    public static function listFromArray($content_arr, $args)
    {
        $list_type = $list_class = $item_class = '';
        $attr = [];
        $defaults = [
            'list_type' => 'ul',
            'list_class' => null,
            'item_class' => null,
            'attr' => null // array('attr-name'=>'value')
        ];
        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);
        $ret = [];
        $ret[] = '<' . $list_type;
        if (!empty($list_class)) {
            $ret[] = ' class="' . $list_class . '"';
        }
        if (!empty($attr)) {
            foreach ($attr as $name => $value) {
                $ret[] = ' ' . $name . '="' . $value . '"';
            }
        }
        $ret[] = '>';
        foreach ($content_arr as $item) {
            $ret[] = '<li';
            if (!empty($item_class)) {
                $ret[] = ' class="' . $item_class . '"';
            }
            $ret[] = '>' . $item . '</li>';
        }
        $ret[] = '</' . $list_type . '>';

        return implode($ret);
    }

    /**
     * Shortcut function to grab glyphicon or fontawesome html
     *
     * @param string $identifier
     * @param bool $fontawesome
     *
     * @return string
     */
    public static function icon($identifier, $fontawesome = true)
    {
        if ($fontawesome) {
            return '<i class="fa fa-' . $identifier . '"></i>';
        } else {
            return '<span class="glyphicon glyphicon-' . $identifier . '"></span>';
        }
    }

    /**
     * Convert associative array to html attribute string.
     *
     * @param array $arr
     * @param string $glue
     *
     * @return string
     */
    public static function arrayToAttributes($arr, $glue = ' ')
    {
        $ret = [];
        foreach ($arr as $name => $attr) {
            if (is_array($attr)) {
                $attr = implode($glue, $attr);
            }
            $ret[] = !is_null($attr) ? $name . '="' . esc_attr($attr) . '"' : $name;
        }

        return implode(' ', $ret);
    }

    public static function wrapLink($content, $url, $attributes = [])
    {
        if ($url) {
            $attributes = array_merge($attributes, ['href' => $url]);
            $content = static::wrapElement($content, 'a', $attributes);
        } elseif (!$url && $attributes['disabled']) {
            $content = static::wrapElement($content, 'a', $attributes);
        }
        return $content;
    }

    public static function wrapElement($content, $tag, $attributes = [])
    {
        $attributes = static::arrayToAttributes($attributes);
        return $content = sprintf(
            '<%1$s%2$s>%3$s</%1$s>',
            $tag,
            $attributes ? ' ' . $attributes : '',
            $content
        );
    }

    public static function permalinkBySlug($slug)
    {
        return get_permalink(self::getIdBySlug($slug));
    }

    public static function getIdBySlug($page_slug)
    {
        $page = get_page_by_path($page_slug);
        if ($page) {
            return $page->ID;
        } else {
            return null;
        }
    }

    public static function locationByIp($ip = null)
    {
        if (!$ip) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return;
        }
        $get = wp_remote_get('http://freegeoip.net/json/' . $ip);
        if (empty($get)) {
            return;
        }
        $json = $get['body'];
        $location = json_decode($json);

        return $location;
    }

    public function generateRandomPosts($args = null)
    {
        $number = $min_title = $max_title = $min_body = $max_body = $body_paragraphs = $headers = $lists = $plaintext = $post_type = $rand_cats = '';
        $defaults = [
            'number' => 20,
            'min_title' => 4,
            'max_title' => 15,
            'min_body' => 0,
            'max_body' => 3,
            'body_paragraphs' => 5,
            'headers' => false,
            'lists' => false,
            'plaintext' => false,
            'post_type' => 'post',
            'rand_cats' => true
        ];
        $args = wp_parse_args($args, $defaults);
        extract($args, EXTR_SKIP);
        $params = $plaintext ? 'plaintext' : 'decorate link';
        $params .= $headers ? ' headers' : null;
        $params .= $lists ? ' ul ol bq' : null;
        $params .= ' prude';
        if ($post_type == 'post' && $rand_cats) {
            $categories = get_categories();
            $cat_list = [];
            foreach ($categories as $category) {
                $cat_list[] = $category->term_id;
            }
        }
        $my_posts = [];
        $length = ['short', 'medium', 'long', 'longer'];
        for ($i = 1; $i <= $number; $i++) {
            $my_posts[] = [
                'post_title' => self::getLastWords(self::placeholderText('1 long plaintext prude'), rand($min_title, $max_title)),
                'post_content' => trim(self::placeholderText(rand(2, $body_paragraphs) . ' ' . $length[rand($min_body, $max_body)] . ' ' . $params)),
                'post_status' => 'publish',
                'post_type' => $post_type,
                'post_category' => !empty($cat_list) ? [$cat_list[rand(0, count($cat_list) - 1)]] : null
            ];
        }
        foreach ($my_posts as $my_post) {
            wp_insert_post($my_post);
        }
    }

    public static function log($var, $id = null)
    {
        if (is_array($var) || is_object($var)) {
            $var = stripcslashes(json_encode($var));
        }
        error_log($id ? $id . ': ' . $var : $var);
    }

    /**
     * Diff two arrays. Gets everything from the second array that is different from the first array.
     * $defaults functions as a whitelist, anything not set will be ignored.
     * Similar but opposite to wp_parse_args().
     *
     * @param array $defaults
     * @param array $args
     *
     * @return array|bool
     */
    public static function parseDiff($args, $defaults)
    {
        if (!is_array($args) || !is_array($defaults)) {
            return $args == $defaults ? '' : $args;
        }
        $difference = [];
        $key_diff = array_diff_key($defaults, $args);
        if (!empty($key_diff)) {
            foreach (array_keys($key_diff) as $item) {
                $args[$item] = [];
            }
        }
        $comp1 = static::stringifyArray($defaults);
        $comp2 = static::stringifyArray($args);
        foreach ($comp1 as $key => $value) {
            if (is_array($comp1[$key]) || is_array($comp2[$key])) {
                if (json_encode($comp1[$key]) != json_encode($comp2[$key])) {
                    $difference[$key] = $args[$key];
                }
            } else if ($comp1[$key] != $comp2[$key]) {
                $difference[$key] = $args[$key];
            }
        }

        return $difference;
    }

    /**
     * Recursively typecast all array values to strings.
     *
     * @param $array
     *
     * @return array
     */
    public static function stringifyArray($array)
    {
        $new = [];
        foreach ($array as $key => $value) {
            $new[$key] = is_array($value) ? static::stringifyArray($value) : strval($value);
            if (!isset($new[$key]) || $new[$key] === "false" || $new[$key] === "null") {
                $new[$key] = [];
            }
        }

        return $new;
    }

    /**
     * Typecast numeric values in arrays. Useful for parsing ajax requests.
     *
     * @param $array
     *
     * @return array
     */
    public static function numerifyArray($array)
    {
        $new = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $new[$key] = static::numerifyArray($value);
            } else {
                if (is_numeric($value)) {
                    $new[$key] = $value + 0;
                } else {
                    $new[$key] = $value;
                }
            }
        }

        return $new;
    }

    /**
     * @param string $file Path of template file.
     * @param array $scope Scope associative array - ['my_var' => 'hello world'] is extracted to $my_var = 'hello world'
     *                          inside the referenced template.
     *
     * @return null|string
     */
    public static function getFileContents($file, $scope = [])
    {
        if (!file_exists($file)) {
            return null;
        }
        if (is_object($scope)) {
            $scope = (array)$scope;
        }
        extract($scope, EXTR_SKIP);
        ob_start();
        include($file);

        return ob_get_clean();
    }

    /**
     * @param string|array $template_names Locate template (or first valid template file in array).
     * @param array $scope Scope associative array - ['my_var' => 'hello world'] is extracted to $my_var = 'hello world'
     *                                     inside the referenced template.
     * @param string|array $base_path A base path for the template path, assumes theme if not provided
     *
     * @return null|string
     */
    public static function getTemplateScoped($template_names, $scope = [], $base_path = '')
    {
        $file = static::locateTemplate($template_names, $base_path);

        return $file ? self::getFileContents($file, $scope) : null;
    }

    /**
     * Will try to locate first viable template file based on array of relative file paths against an array of base paths.
     * so ([file1, file2],[base1, base2]) will find the first match of base1/file1, base1/file2, base2/file1, etc.
     * If no base path is provided, assumes either full pathname or current theme directory.
     *
     * @param array|string $template_names
     * @param array|string $base_paths
     *
     * @return bool|string
     */
    public static function locateTemplate($template_names, $base_paths = '')
    {
        $default_extension = 'php';
        $extension_whitelist = [
            'php',
            'html',
            'blade'
        ];
        $template_names = is_array($template_names) ? array_filter($template_names) : [$template_names];
        $base_paths = is_array($base_paths) ? $base_paths : [$base_paths];
        $base_paths = array_filter($base_paths);
        $template_names = array_map(function ($name) use ($default_extension, $extension_whitelist) {
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            return (in_array($ext, $extension_whitelist)) ? $name : $name . '.' . $default_extension;
        }, $template_names);

        foreach ($template_names as $template_name) {
            //if full file path already exists, use it
            if (file_exists($template_name)) {
                return $template_name;
            }
            //check in active theme folder
            if ($theme_template = locate_template($template_name)) {
                return $theme_template;
            }
            //then, try all supplied base paths
            foreach ($base_paths as $base_path) {
                $filename = implode('/', [rtrim($base_path, '/'), $template_name]);
                if (file_exists($filename)) {
                    return $filename;
                }
            }
        }
        return false;
    }

    public static function toCamelCase($symbol_name)
    {
        return lcfirst(static::toPascalCase($symbol_name));
    }

    public static function toPascalCase($symbol_name)
    {
        return str_replace('_', '', ucwords($symbol_name, '_'));
    }

    public static function toSnakeCase($symbol_name)
    {
        return ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $symbol_name)), '_');
    }

    public static function getMethodName($object, $name, $prefix = 'get')
    {
        $method_name = $prefix ? sprintf('%s_%s', $prefix, $name) : $name;
        //underscore/snake-case format
        if (method_exists($object, $method_name)) {
            return $method_name;
        }
        //camel-case format
        $camel_case_method_name = Util::toCamelCase($method_name);
        if (method_exists($object, $camel_case_method_name)) {
            return $camel_case_method_name;
        }
        return false;
    }

    public static function mapPostIds(array $posts)
    {
        return array_map(function ($post) {
            return $post->ID;
        }, $posts);
    }

    public static function acfLinkToAttr(array $field, array $atts = []): array
    {
        $field = $field ?: [];
        $field['href'] = $field['url'] ?? '';
        unset ($field['url']);

        return array_filter(array_merge($field, $atts));
    }

    public static function acfLinkToEl(array $field, array $atts = [], string $text = null): string
    {
        $field = static::acfLinkToAttr($field, $atts);
        if ($text === null && !empty($field['title'])) {
            $text = $field['title'];
            // Remove title attr because it isn't helping @see https://silktide.com/i-thought-title-text-improved-accessibility-i-was-wrong/
            unset ($field['title']);
        }

        return '<a ' . static::arrayToAttributes($field) . '>' . $text . '</a>';
    }

    public static function acfClearPostStore($post_id)
    {
        $acf_store = acf_get_store('values');
        $acf_store->data = array_filter($acf_store->data, function ($key) use ($post_id) {
            return false === strpos($key, "$post_id");
        }, ARRAY_FILTER_USE_KEY);
    }

    public static function dump($d, $return = false)
    {
        if (class_exists('Kint')) {
            \Kint::$display_called_from = false;
            \Kint::$expanded = true;
            \Kint::$return = true;
            if ($return) {
                return \Kint::dump($d);
            } else {
                echo \Kint::dump($d);
            }
        } else {
            if ($return) {
                return '<pre>' . print_r($d, true) . '</pre>';
            } else {
                echo '<pre>' . print_r($d, true) . '</pre>';
            }
        }
    }

    public static function excerpt(\WP_Post $post, int $num_words = 0, $raw = false)
    {
        $excerpt_length = $num_words ?: apply_filters('excerpt_length', 20);
        $raw_excerpt = $post->post_excerpt ?: $post->post_content;
        $text = strip_shortcodes($raw_excerpt);

        /* Apply shortcode-parsing if content might be within shortcodes */
        if (empty($text) && !empty($raw_excerpt)) {
            $text = strip_tags(do_shortcode($raw_excerpt));
        }

        /* Set missing global normally present within the loop */
        if (empty($GLOBALS['pages'])) {
            $GLOBALS['pages'] = [''];
        }
        if (empty($GLOBALS['page'])) {
            $GLOBALS['page'] = 1;
        }
        /* Temporarily switch global post to a dummy one */
        $global_post = $GLOBALS['post'] ?? null;
        $GLOBALS['post'] = (object)['post_content' => ''];

        /* Modified from wp_trim_excerpt() */
        if (!$raw) {
            $text = apply_filters('the_content', $text);
        }
        $text = str_replace(']]>', ']]&gt;', $text);
        $excerpt_more = $raw ? null : apply_filters('excerpt_more', '');
        $text = wp_trim_words($text, $excerpt_length, $excerpt_more);
        if (!$raw) {
            $text = apply_filters('get_the_excerpt', $text, $post);
        }
        /* Switch back */
        $GLOBALS['post'] = $global_post;

        return $text;
    }

    public static function clearCache()
    {
        CacheController::flagCacheClear();
    }

    /**
     * @param string $base Component container name.
     * @param array $class_modifiers
     * @return string
     */
    public static function componentClasses($base, $class_modifiers = []): string
    {
        if (empty($class_modifiers)) {
            return $base;
        }
        return sprintf(
            '%1$s %2$s',
            $base,
            implode(' ', preg_filter(
                '/^/',
                "{$base}--",
                array_filter($class_modifiers)
            ))
        );
    }

    /**
     * @param string $base Component container name.
     * @param array $class_modifiers
     * @param array $element_attributes
     * @return string
     */
    public static function componentAttributes($base, $class_modifiers = [], $element_attributes = []): string
    {
        return static::arrayToAttributes(
            static::componentAttributesArray($base, $class_modifiers, $element_attributes)
        );
    }

    /**
     * @param $base
     * @param array $class_modifiers
     * @param array $element_attributes
     * @return array
     */
    public static function componentAttributesArray($base, $class_modifiers = [], $element_attributes = [])
    {
        $core = [
            'data-gtm' => $base
        ];
        if ($classes = static::componentClasses($base, $class_modifiers)) {
            $core['class'] = $classes;
        }
        if (isset($element_attributes['class'])) {
            $core['class'] = $core['class'] . " {$element_attributes['class']}";
            unset($element_attributes['class']);
        }
        if (!empty($element_attributes)) {
            $core = array_merge($core, $element_attributes);
        }
        return $core;
    }

    /**
     * Builds full dist path to theme asset
     *
     * @param $filename
     * @return string
     */
    public static function getAssetPath($filename)
    {
        static $manifest;

        $dist_path = get_stylesheet_directory_uri() . '/dist/';
        $dist_dir = get_stylesheet_directory() . '/dist/';
        $file_path = trailingslashit(trim(dirname($filename), '.'));
        $file = basename($filename);

        if (empty($manifest)) {
            $manifest_path = $dist_dir . 'assets.json';
            $manifest = new JsonManifest($manifest_path);
        }

        $manifest_paths = $manifest->get();
        $found_path = $manifest_paths[$file] ?? $manifest_paths[$filename] ?? false;

        if (!$found_path) {
            return $dist_path . $filename;
        }

        if (file_exists($dist_dir . $file_path . $found_path)) {
            return $dist_path . $file_path . $found_path;
        }

        return $dist_path . $found_path;
    }

    public static function randomString($length = 10, $replace_key = '')
    {
        $key = $replace_key ? $replace_key : '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($key, ceil($length / strlen($key)))), 1, $length);
    }
}