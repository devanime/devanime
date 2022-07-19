<?php
/**
 * Class Term_Base
 * @package Backstage\Models
 * @author  Jeremy Strom <jeremy.strom@gmail.com>
 * @version 1.0
 */

namespace Backstage\Models;

class TermBase {

    const TAXONOMY = null;
    protected static $default_args = [];
    protected $_fields = [];
    private $_term, $_term_init, $_permalink;

    /**
     * Term_Base constructor.
     *
     * @param $term
     */
    public function __construct($term) {
        $this->_term_init = $term;
        $this->init();
    }

    protected function init() {
    }

    public function permalink() {
        if (empty($this->_permalink)) {
            $this->_permalink = get_term_link($this->term(), $this->taxonomy());
        }

        return $this->_permalink;
    }

    public function taxonomy()
    {
        return static::TAXONOMY ?: $this->term()->taxonomy;
    }

    public function field($selector) {
        if (empty($this->_fields[$selector])) {
            $this->_fields[$selector] = get_field($selector, $this->getIdForField());
        }

        return $this->_fields[$selector];
    }

    public function fields() {
        $this->_fields = get_fields($this->getIdForField());

        return $this->_fields;
    }

    /**
     * @return \WP_Term
     */
    public function term() {
        if (empty($this->_term)) {
            $taxonomy = $this->_term_init->taxonomy ?? static::TAXONOMY;
            $this->_term = get_term($this->_term_init, $taxonomy);
            unset($this->_term_init);
        }

        return $this->_term;
    }

    public function isValid() {
        return $this->term() instanceof \WP_Term;
    }

    /**
     * @param \WP_Post $post
     *
     * @return static[]
     */
    public static function getByPost($post) {
        $terms = get_the_terms($post, static::TAXONOMY);
        $ret = [];
        if (empty($terms) || is_wp_error($terms)) {
            return $ret;
        }
        foreach ($terms as $term) {
            $ret[] = new static($term);
        }

        return $ret;
    }

    /**
     * @param array $args
     *
     * @return static[]
     */
    public static function getTerms($args = []) {
        $defaults = [
            'taxonomy' => static::TAXONOMY,
        ];
        $defaults = wp_parse_args(static::$default_args, $defaults);
        $args = wp_parse_args($args, $defaults);
        $terms = get_terms($args);
        $ret = [];
        if (empty($terms) || is_wp_error($terms)) {
            return $ret;
        }

        foreach ($terms as $term) {
            $ret[] = new static($term);
        }

        return $ret;
    }

    protected function getIdForField()
    {
        return $this->taxonomy() . '_' . $this->term()->term_id;
    }
}