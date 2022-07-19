<?php
/**
 * Class Tax_Args
 * @package DevAnime\Taxonomies
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\Taxonomies;

use DevAnime\Util;

class TaxonomyArgs {

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

        return [
            'name'                       => $plural,
            'singular_name'              => $singular,
            'menu_name'                  => $plural,
            'name_admin_bar'             => $singular,
            'add_new'                    => 'Add New',
            'add_new_item'               => sprintf('Add New %s', $singular),
            'edit_item'                  => sprintf('Edit %s', $singular),
            'new_item'                   => sprintf('New %s', $singular),
            'view_item'                  => sprintf('View %s', $singular),
            'update_item'                => sprintf('Update %s', $singular),
            'new_item_name'              => sprintf('New %s Name', $singular),
            'search_items'               => sprintf('Search %s', $plural),
            'not_found'                  => sprintf('No %s found.', $plural_lcase),
            'all_items'                  => sprintf('All %s', $plural),
            'popular_items'              => sprintf('Popular %s', $plural),
            'separate_items_with_commas' => sprintf('Separate %s with commas', $plural_lcase),
            'add_or_remove_items'        => sprintf('Add or remove %s', $plural_lcase),
            'choose_from_most_used'      => sprintf('Choose from most used remove %s', $plural_lcase),

       ];
    }
}