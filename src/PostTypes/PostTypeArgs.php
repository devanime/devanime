<?php
/**
 * Class CPT_Args
 * @package Backstage\Custom_Post_Types
 * @author  Jeremy Strom <jeremy.strom@gmail.com>
 * @version 1.0
 */

namespace Backstage\PostTypes;

use Backstage\Util;

class PostTypeArgs {
    public $labels;
    public $args;

    public function __construct($labels, $args) {
        $this->labels = wp_parse_args($labels, $this->getDefaultLabels($labels));
        $this->args = $args;
        $this->args['labels'] = $this->labels;
    }

    private function getDefaultLabels($labels) {
        $singular = $labels['singular_name'];
        $singular_lcase = strtolower($singular);
        $plural = $labels['name'] ?? Util::pluralize($singular);
        $plural_lcase = strtolower($plural);
        $featured_image = $labels['featured_image'] ?? 'Featured Image';
        $featured_image_lcase = strtolower($featured_image);

        return [
            'name'                  => $plural,
            'singular_name'         => $singular,
            'menu_name'             => $plural,
            'name_admin_bar'        => $singular,
            'add_new'               => 'Add New',
            'add_new_item'          => sprintf('Add New %s', $singular),
            'edit_item'             => sprintf('Edit %s', $singular),
            'new_item'              => sprintf('New %s', $singular),
            'view_item'             => sprintf('View %s', $singular),
            'search_items'          => sprintf('Search %s', $plural),
            'not_found'             => sprintf('No %s found.', $plural_lcase),
            'not_found_in_trash'    => sprintf('No %s found in trash.', $plural_lcase),
            'parent_item_colon'     => sprintf('Parent %s:', $singular),
            'all_items'             => sprintf('All %s', $plural),
            'archives'              => sprintf('%s Archives', $singular),
            'insert_into_item'      => sprintf('Insert into %s', $singular_lcase),
            'uploaded_to_this_item' => sprintf('Uploaded to this %s', $singular_lcase),
            'filter_items_list'     => sprintf('Filter %s list', $plural_lcase),
            'items_list_navigation' => sprintf('%s list navigation', $plural),
            'items_list'            => sprintf('%s list', $plural),
            "featured_image"        => $featured_image,
            "set_featured_image"    => sprintf('Set %s', $featured_image_lcase),
            "remove_featured_image" => sprintf('Remove %s', $featured_image_lcase),
            "use_featured_image"    => sprintf('Use %s', $featured_image_lcase)
        ];
    }
}