<?php

namespace DevAnime\Register\PostType;

/**
 * class PostTypeAdminColumns
 * @package DevAnime\Register\PostType
 */
class PostTypeAdminColumns
{
    private $slug, $register, $columns = [];

    public function __construct($slug, PostTypeArguments $register)
    {
        $this->slug = $slug;
        $this->register = $register;
        add_action('manage_edit-' . $this->slug . '_sortable_columns', [$this, 'sortableColumns']);
    }

    public function init($columns)
    {
        $this->columns = $columns;
        add_filter('manage_' . $this->slug . '_posts_columns', [$this, 'columnHeaders']);
        add_action('manage_' . $this->slug . '_posts_custom_column', [$this, 'columnContent'], 10, 2);
        add_action('admin_print_styles', [$this, 'printAdminStyles']);
    }

    /**
     * Customize admin columns, but ensure cb, title, and date remain in the column list if not otherwise specified.
     *
     * @param array $columns
     *
     * @return array
     */
    public function columnHeaders($columns)
    {
        $headers = $taxonomies = [];

        foreach ($this->columns as $slug => $column) {
            $tax_slug = 'taxonomy-' . $slug;
            $slug = array_key_exists($tax_slug, $columns) ? $tax_slug : $slug;
            $headers[$slug] = is_array($column) ? $column['label'] : $column;
        }
        foreach ($columns as $slug => $column) {
            if (strpos($slug, 'taxonomy-') !== false) {
                $taxonomies[] = $slug;
            }
        }
        $get_defaults = function ($arr) use ($columns, $headers) {
            /* Pulls default columns out if exists in custom columns list */
            return array_intersect_key($columns, array_diff_key(array_flip($arr), $headers));
        };

        $columns = empty($headers) ?
            $columns :
            array_merge($get_defaults([
                'cb',
                'title'
            ]), $headers, $get_defaults($taxonomies), $get_defaults(['author', 'comments', 'date']));
        $columns = apply_filters("devanime/admin_columns", $columns, $this->slug);

        return $columns;
    }

    public function printAdminStyles()
    {
        if (apply_filters('devanime/print_admin_styles', true)) {
            $screen = get_current_screen();
            if ($screen->id === 'edit-' . $this->slug) {
                echo '<style type="text/css">';
                echo apply_filters('devanime/print_admin_styles/' . $this->slug, '.column-thumbnail { text-align: center; width:75px; } .column-thumbnail img{ display:block;margin: 0 auto;max-width:100%; height:auto; }');
                echo '</style>';
            }
        }
    }

    public function columnContent($column_id, $post_id)
    {
        if (!array_key_exists($column_id, $this->columns)) {
            return false;
        }
        $filter_base = 'devanime/admin_col';
        $content = '';
        /**
         * add_filter('devanime/admin_col', 'my_func', 10, 4);
         * function my_func($content, $post_id, $column_id, $post_type){ return $content; }
         */
        $content = apply_filters($filter_base, $content, $post_id, $column_id, $this->slug);
        /**
         * add_filter('devanime/admin_col/{{column_key}}', 'my_func', 10, 3);
         * function my_func($content, $post_id, $post_type){ return $content; }
         */
        $content = apply_filters($filter_base . '/' . $column_id, $content, $post_id, $this->slug);
        /**
         * add_filter('devanime/admin_col/{{column_key}}/{{post_type}}', 'my_func', 10, 2);
         * function my_func($content, $post_id){ return $content; }
         */
        $content = apply_filters($filter_base . '/' . $column_id . '/' . $this->slug, $content, $post_id);
        if (is_array($this->columns[$column_id]) && !empty($this->columns[$column_id]['content_filter'])) {
            /**
             * add_filter('my_custom_filter_name', 'my_func', 10, 2);
             * function my_func($content, $post_id){ return $content; }
             */
            $content = apply_filters($this->columns[$column_id]['content_filter'], $content, $post_id);
        }
        if (is_array($content)) {
            $content = implode(' | ', $content);
        }
        echo $content;
    }

    public function sortableColumns($columns)
    {
        if (!empty($this->register->args['taxonomies'])) {
            if (is_array($this->register->args['taxonomies'])) {
                foreach ($this->register->args['taxonomies'] as $taxonomy) {
                    $columns['taxonomy-' . $taxonomy] = 'taxonomy-' . $taxonomy;
                }
            } elseif (is_string($this->register->args['taxonomies'])) {
                $columns['taxonomy-' . $this->register->args['taxonomies']] = 'taxonomy-' . $this->register->args['taxonomies'];
            }
        }
        foreach ($this->columns as $key => $column) {
            if (!empty($column['sortable'])) {
                $columns[$key] = $key;
            }
        }

        return $columns;
    }
}