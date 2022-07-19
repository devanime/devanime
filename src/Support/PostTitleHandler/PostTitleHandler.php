<?php

namespace Backstage\Support\PostTitleHandler;

use Backstage\Models\PostFactory;

/**
 * Class PostTitleHandler
 * @package Backstage\Support
 * @author Vincent Ragosta <vragosta@situationinteractive.com>
 * @version 1.0
 */
class PostTitleHandler
{
    public function __construct()
    {
        add_action('after_save_post', [$this, 'updateGeneratedPostTitle']);
    }

    public function updateGeneratedPostTitle($post_id)
    {
        global $wpdb;
        try {
            $Post = PostFactory::create($post_id);
            $post_status = $Post->post()->post_status;
            $post_type = $Post::POST_TYPE;
            $post_parent = $Post->post()->post_parent;
            if ($Post instanceof HasGeneratedTitle) {
                $title = $Post->getGeneratedTitle();
                $data = ['post_title' => $title];
                if ($Post instanceof HasGeneratedSlug) {
                    $data['post_name'] = wp_unique_post_slug(
                        sanitize_title($Post->getTitleForGeneratedSlug(), $post_id),
                        $post_id, $post_status, $post_type, $post_parent
                    );
                }
                $wpdb->update($wpdb->posts, $data, ['ID' => $post_id]);

                clean_post_cache($post_id);
            }

        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
