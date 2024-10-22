<?php
/**
 * Class CPT_Handler
 * @package DevAnime\Custom_Post_Types
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\PostTypes;

use DevAnime\Options\RegisterOption;
use DevAnime\Util;

class PostTypeHandler {

    protected $slug, $register, $extras, $admin_columns, $query_filter;

    public function __construct($slug, $data) {
        $this->slug = $slug;
        $this->register = new PostTypeArgs($data['labels'], $data['args']);
        $this->extras = $data['extras'] ?? [];
        $this->admin_columns = new PostTypeAdminColumns($this->slug, $this->register);
        $this->query_filter = new PostTypeSort($this->slug, $this->register);
        if (empty($this->extras['admin_columns'])) {
            $this->extras['admin_columns'] = [];
        }
        foreach ($this->extras as $key => $args) {
            $method_name = Util::toCamelCase($key);
            if (method_exists($this, $method_name)) {
                call_user_func([$this, $method_name], $args);
            }
        }
        add_action('init', [$this, 'registerPostType'], 8);
    }

    public function registerPostType() {
        if (!post_type_exists($this->slug)) {
            register_post_type($this->slug, $this->register->args);
        }
    }

    protected function defaultSort($args) {
        $this->query_filter->setDefaultSort($args);
    }

    protected function adminSort($args) {
        $this->query_filter->setAdminSort($args);
    }

    protected function titlePlaceholder($title) {
        if (empty($title) || !is_string($title)) {
            return;
        }
        add_filter('enter_title_here', function ($default, $post) use ($title) {
            return $post->post_type == $this->slug ? $title : $default;
        }, 10, 2);
    }

    protected function adminColumns($args) {
        new AdminColumnContent();
        $this->admin_columns->init($args);
        $this->query_filter->setColumns($args);
        new AdminFilters($this->slug, $args, $this->register);
    }

    protected function acfSettings($args) {
        if (is_string($args)) {
            $args = ['page_title' => $args];
        }
        if (is_bool($args)) {
            $args = ['page_title' => $this->register->labels['singular_name'] . ' Settings'];
        }
        if (is_array($args)) {
            $defaults = ['parent_slug' => 'edit.php?post_type=' . $this->slug];
            $args = wp_parse_args($args, $defaults);
            new RegisterOption($args);
        }
    }

    protected function cmspoLabel($label) {
        $this->query_filter->cmspoLabel($label);
    }
}