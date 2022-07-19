<?php
/**
 * Class PostBase
 * @package DevAnime\Models
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 2.0
 */

namespace DevAnime\Models;

use DevAnime\Support\DateTime;
use DevAnime\Util;
use WP_Image;

/**
 * @property $ID
 * @property $post_title
 * @property $post_excerpt
 * @property $post_content
 */
abstract class PostBase {

    const POST_TYPE = null;
    private $_post_init = null;
    private $_post;
    private $_author;
    private $_fields = [], $_terms = [];
    protected $_permalink, $_featured_image;
    protected $_date_field_names = [];
    protected static $default_query = [];
    protected $_default_date_format;

    function __construct($post = null) {
        $this->_post_init = $post;
        $this->init();
    }

    protected function init() {
    }

    public function reset($reload = false) {
        $post_id = $this->post()->ID;
        $this->_post = $this->_author = $this->_permalink = $this->_featured_image = null;
        $this->_fields = $this->_terms = [];
        $this->_post_init = $post_id;
        Util::acfClearPostStore($post_id);
        if ($reload) {
            $this->post();
            $this->fields(true);
            $this->allTermIdsByTaxonomy();
            $this->permalink();
            $this->featuredImage();
        }
        $this->init();
    }

    public function permalink() {
        if (empty($this->_permalink)) {
            $this->_permalink = get_permalink($this->post());
        }

        return $this->_permalink;
    }

    public function publishedDate( $default_format = null )
    {
        $format = $default_format ?: $this->_default_date_format;
        return new DateTime( $this->post()->post_date, $format );
    }

    public function modifiedDate( $default_format = null )
    {
        $format = $default_format ?: $this->_default_date_format;
        return new DateTime( $this->post()->post_modified, $format );
    }

    /**
     * @return WP_Image
     */
    public function featuredImage() {
        if (!isset($this->_featured_image) && class_exists('WP_Image')) {
            $this->_featured_image = WP_Image::get_featured($this->post());
        }
        return $this->_featured_image;
    }

    public function setFeaturedImage(WP_Image $image) {
        $this->_featured_image = $image;
    }

    public function field($selector) {
        if (empty($this->_fields[$selector])) {
            $field = get_field($selector, $this->post()->ID);
            if (in_array($selector, $this->_date_field_names)) {
                $field = $field ? new DateTime($field, $this->_default_date_format) : null;
            }
            $this->_fields[$selector] = $field;
        }

        return $this->_fields[$selector];
    }

    public function fields($fetch = true) {
        if ($fetch) {
            $fields = get_fields($this->post()->ID);
            foreach ((array) $fields as $key => $value) {
                $this->$key;
            }
        }

        return $this->_fields;
    }

    /**
     * @return bool|null|\WP_Post
     */
    public function post() {
        if (empty($this->_post)) {
            $this->_post = $this->hasValidPostInit() ?
                get_post($this->_post_init) :
                (object) ['ID' => null, 'post_type' => static::POST_TYPE];
            if (!$this->isValidPostInit()) {
                throw new \InvalidArgumentException( sprintf(
                    'Invalid post initialization for post type "%s" with id: %d',
                    static::POST_TYPE,
                    $this->_post->ID
                ));
            }
            $this->_post_init = null;
        }

        return $this->_post;
    }

    protected function isValidPostInit()
    {
        return $this->_post->post_type == static::POST_TYPE;
    }

    public function title()
    {
        return get_the_title($this->post());
    }

    public function content($use_global = true)
    {
        $content = $this->isGlobal() && $use_global ?
            get_the_content() :
            $this->post()->post_content;
        return apply_filters('the_content', $content);
    }

    public function type()
    {
        return static::POST_TYPE ?: $this->post()->post_type;
    }

    public function isGlobal()
    {
        return isset($GLOBALS['post']) && $GLOBALS['post'] == $this->post();
    }

    public function excerpt($num_words = 0, $raw = false) {
        return Util::excerpt($this->post(), $num_words, $raw);
    }

    /**
     * @return \WP_User|false
    */
    public function author() {
        if (empty($this->_author)) {
            $post = $this->post();
            if (!$post) return false;
            $this->_author = new \WP_User($post->post_author);
        }
        return $this->_author;
    }

    public function setAuthor(\WP_User $user)
    {
        $this->_author = $user;
        $this->post()->post_author = $user->ID;
    }

    /**
     * @param string $taxonomy
     *
     * @return \WP_Term[]
     */
    public function terms($taxonomy) {
        if (! (isset($this->_terms[$taxonomy]) && is_array($this->_terms[$taxonomy]))) {
            $terms = get_the_terms($this->post(), $taxonomy);
            if (!is_array($terms)) $terms = [];
            $this->_terms[$taxonomy] = $terms;
        }

        return $this->_terms[$taxonomy];
    }

    /**
     * @return array
     */
    public function allTermIdsByTaxonomy() {
        $taxonomies = get_object_taxonomies($this->type(), 'objects');
        foreach ($taxonomies as $name => &$term_ids) {
            $term_ids = array_map(function(\WP_Term $term) {
                return $term->term_id;
            } , $this->terms($name));
        }
        return $taxonomies;
    }

    public function isValid() {
        return $this->post() instanceof \WP_Post;
    }

    /**
     * @param array $args
     *
     * @return array
     */
    protected static function getQuery($args = []) {
        $defaults = [
            'post_type'        => static::POST_TYPE,
            'post_status'      => 'publish',
            'posts_per_page'   => - 1,
            'offset'           => 0,
            'suppress_filters' => true
        ];
        if (current_user_can('read_private_posts')) {
            $defaults['post_status'] = ['publish', 'private'];
        }
        $defaults = wp_parse_args(static::getDefaultQuery(), $defaults);
        return wp_parse_args($args, $defaults);
    }

    public static function getDefaultQuery() {
        return static::$default_query;
    }

    /**
     * @param array $args
     *
     * @return static[]
     */
    public static function getPosts($args = []) {
        $args = static::getQuery($args);
        $query = new \WP_Query();
        $posts = $query->query($args);
        $ret = [];
        foreach ($posts as $post_obj) {
            $ret[] = static::create($post_obj);
        }
        return $ret;
    }

    /**
     * @return static
     */
    public static function create($post_obj) {
        return new static($post_obj);
    }

    /**
     * @return static
     */
    public static function createFromGlobal() {
        return static::create($GLOBALS['post']);
    }

    /**
     * Shortcut to post, term and field properties
     *
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        if ($method_name = Util::getMethodName($this, $name)) {
            if (!isset($this->_fields[$name])) {
                $value = $this->{$method_name}();
                $this->_fields[$name] = $value;
            }
            return $this->_fields[$name];
        }
        $post = $this->post();
        if (property_exists($post, $name)) return $post->{$name};

        if ($terms = $this->terms($this->getTaxonomyFromProperty($name))) return $terms;

        return $this->field($name);
    }

    public function __set($name, $value) {
        if ($method_name = Util::getMethodName($this, $name, 'set')) {
            return $this->{$method_name}($value);
        }
        if (property_exists('WP_Post', $name)) {
            $this->post()->{$name} = $value;
            return;
        }

        if ($taxonomy = $this->getTaxonomyFromProperty($name)) {
            $this->_terms[$taxonomy] = array_map(function($value) use ($taxonomy) {
                return is_string($value) ?
                    get_term_by('slug', $value, $taxonomy) :
                    get_term($value, $taxonomy);
            }, (array) $value);
            return;
        }

        $this->_fields[$name] = $value;
    }

    public function __isset($name) {
        $value = $this->$name;
        return !empty($value);
    }

    private function getTaxonomyFromProperty($name) {
        if (taxonomy_exists($name)) return $name;

        $singular_name = Util::singularize($name);
        return taxonomy_exists($singular_name) ? $singular_name : false;
    }

    private function hasValidPostInit() {
        return (
            is_numeric($this->_post_init) ||
            $this->_post_init instanceof \WP_Post
        );
    }
}