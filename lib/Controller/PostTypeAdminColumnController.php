<?php

namespace DevAnime\Controller;

/**
 * class PostTypeAdminColumnController
 * @package DevAnime\Controller
 */
class PostTypeAdminColumnController
{
    public function __construct()
    {
        add_filter('devanime/admin_col', [$this, 'postMeta'], 5, 3);
        add_filter('devanime/admin_col/thumbnail', [$this, 'postThumbnail'], 5, 2);
        add_filter('devanime/admin_col/editor', [$this, 'editor'], 5, 2);
        add_filter('devanime/admin_col/excerpt', [$this, 'excerpt'], 5, 2);
    }

    public function postMeta($content, $post_id, $column_id)
    {
        return get_post_meta($post_id, $column_id) ?: $content;
    }

    public function postThumbnail($content, $post_id)
    {
        if (!class_exists('WP_Image')) {
            return $content;
        }
        $img = \WP_Image::get_featured($post_id);
        if (empty($img)) {
            return $content;
        }
        if ($img->height > 75) {
            $img->height(75);
        }
        return $img->get_html();
    }

    public function editor($content, $post_id)
    {
        $post = get_post($post_id);
        return strip_tags($post->post_content) ?: $content;
    }

    public function excerpt($content, $post_id)
    {
        $post = get_post($post_id);
        return strip_tags($post->post_excerpt) ?: $content;
    }
}