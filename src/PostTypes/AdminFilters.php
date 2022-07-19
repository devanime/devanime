<?php
/**
 * Class Admin_Filters
 * @package DevAnime\Custom_Post_Types
 * @author  DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */

namespace DevAnime\PostTypes;

class AdminFilters {

    protected $slug, $columns = [];
    /**
     * @var PostTypeArgs
     */
    protected $register;

    public function __construct($slug, $columns, PostTypeArgs $register) {
        $this->slug = $slug;
        $this->columns = $columns;
        $this->register = $register;
        add_action('restrict_manage_posts', [$this, 'renderColumnFilters']);
        add_action('parse_query', [$this, 'filterColumnsByMeta']);
    }

    public function renderColumnFilters() {
        global $typenow;
        if ($typenow !== $this->slug) {
            return false;
        }
        $filters = array_merge($this->getCustomFilters(), $this->getTaxonomyFilters());
        $keys = array_map(function ($val) {
            return $val['id'];
        }, $filters);
        $filters = array_combine($keys, $filters);
        $filters = apply_filters('devanime/admin_filters', $filters, $this->slug);
        $filters = array_filter(array_map(function ($filter) {
            return count($filter['options']) <= 1 ? false : $filter;
        }, $filters));
        if (empty($filters)) {
            return false;
        }
        foreach ($filters as $filter) {
            echo "<select name='{$filter['id']}' id='{$filter['id']}' class='postform'>";
            foreach ($filter['options'] as $option) {
                echo "<option value={$option['value']}";
                echo $option['selected'] ? " selected='selected'" : "";
                echo ">{$option['label']}</option>";
            }
            echo "</select>";
        }
    }

    private function getCustomFilters() {

        /** @var \wpdb $wpdb */
        global $wpdb;
        $filters = [];
        if (empty($this->columns)) {
            return $filters;
        }
        foreach ($this->columns as $id => $column) {
            if (!is_array($column)) {
                continue;
            }
            if (
                (!empty($this->register->args['taxonomies']) && in_array($id, $this->register->args['taxonomies'])) ||
                empty($column['filterable'])
            ) {
                continue;
            }
            $fields = $wpdb->get_results($wpdb->prepare(
                "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key=%s ORDER BY 1", $id
            ));
            if (empty($fields)) {
                continue;
            }
            $filter = compact('id');
            $filter['options'][] = [
                'value'    => '',
                'label'    => "Filter by {$column['label']}",
                'selected' => false
            ];
            array_walk($fields, function ($el) {
                $el->label = (string) $el->meta_value;
            });
            $fields = apply_filters("devanime/admin_filters/$id", $fields, $this->slug);
            foreach ($fields as $field) {
                $filter['options'][] = [
                    'value'    => urlencode($field->meta_value),
                    'label'    => $field->label,
                    'selected' => (bool) (isset($_GET[$id]) && $_GET[$id] == urlencode($field->meta_value))
                ];
            }
            $filters[] = $filter;
        }

        return $filters;
    }

    private function getTaxonomyFilters() {
        $filters = [];
        if (empty($this->register->args['taxonomies'])) {
            return $filters;
        }

        foreach ($this->register->args['taxonomies'] as $tax_slug) {
            if (in_array($tax_slug, ['category', 'post_tag'])) {
                continue;
            }
            $tax_obj = get_taxonomy($tax_slug);
            if (!$tax_obj->show_admin_column) {
                continue;
            }
            $terms = get_terms(['taxonomy' => $tax_slug]);
            if (is_wp_error($terms)) {
                continue;
            }
            $filter = ['id' => $tax_slug];
            $filter['options'][] = [
                'value'    => '',
                'label'    => "All {$tax_obj->labels->name}",
                'selected' => false
            ];
            foreach ($terms as $term) {
                $filter['options'][] = [
                    'value'    => $term->slug,
                    'label'    => $term->name,
                    'selected' => (bool) (isset($_GET[$tax_slug]) && $_GET[$tax_slug] == $term->slug)
                ];
            }
            $filters[] = $filter;
        }

        return $filters;
    }

    public function filterColumnsByMeta(\WP_Query $query) {
        global $pagenow;
        if (! (is_admin() && $pagenow == 'edit.php') ||
            ! $query->is_main_query() ||
            $query->get('post_type') !== $this->slug ||
            empty($this->columns)
        ) {
            return false;
        }
        foreach ($this->columns as $id => $column) {
            if ((!empty($this->register->args['taxonomies']) && in_array($id, $this->register->args['taxonomies'])) || empty($column['filterable'])) {
                continue;
            }
            if (isset($_GET[$id]) && $_GET[$id] != '') {
                /* Add key/value to the meta query to allow multiple meta filters */
                $query->query_vars['meta_query'][] = ['key' => $id, 'value' => urldecode($_GET[$id])];
            }
        }
    }
}