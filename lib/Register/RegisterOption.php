<?php

namespace DevAnime\Register;

/**
 * class RegisterOption
 * @package DevAnime\Register
 */
class RegisterOption
{
    protected $slug, $args, $children;

    public function __construct($data)
    {
        if (!function_exists('acf_add_options_page')) {
            return;
        }
        if (empty($data['page_title'])) {
            return;
        }
        $this->children = $data['child_pages'] ?? [];
        unset($data['child_pages']);
        $defaults = apply_filters('devanime/register_options/defaults', [
            'menu_slug' => sanitize_title('acf-' . $data['page_title']),
            'capability' => 'manage_options'
        ]);
        $this->args = wp_parse_args($data, $defaults);
        if (isset($this->args['position'])) {
            $this->args['position'] = (string)$this->args['position'];
        }
        acf_add_options_page($this->args);
        foreach ($this->children as $slug => $args) {
            acf_add_options_sub_page(wp_parse_args($args, ['parent_slug' => $this->args['menu_slug']]));
        }
    }
}