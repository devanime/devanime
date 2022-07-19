<?php
/**
 * Class Taxonomy_Handler
 * @package Backstage\Taxonomies
 * @author  Jeremy Strom <jeremy.strom@gmail.com>
 * @version 1.0
 */

namespace Backstage\Taxonomies;

class TaxonomyHandler {
    protected $taxonomies;

    public function __construct($slug, $data) {
        $this->slug = $slug;
        $this->post_types = $data['post_types'] ?? [];
        if (is_string($this->post_types)) {
            $this->post_types = [$this->post_types];
        }
        $this->register = new TaxonomyArgs($data['labels'], $data['args']);
        add_action('init', [$this, 'registerTaxonomy'], 8);
    }

    public function registerTaxonomy() {
        if (!taxonomy_exists($this->slug)) {
            register_taxonomy($this->slug, $this->post_types, $this->register->args);
            /* @see https://codex.wordpress.org/Function_Reference/register_taxonomy_for_object_type */
            foreach ($this->post_types as $post_type) {
                register_taxonomy_for_object_type($this->slug, $post_type);
            }
        }
    }
}