<?php

namespace DevAnime\Controller;

/**
 * class PostController
 * @package DevAnime\Controller
 */
class PostController
{
    public function __construct()
    {
        add_filter('update_postmeta', [$this, 'editLockAfterSavePost'], 10, 3);
    }

    public function editLockAfterSavePost($meta_id, $object_id, $meta_key)
    {
        if (!$this->isPostedEditLock($meta_key)) {
            return;
        }
        $post = get_post($object_id);
        do_action('after_save_post', $object_id);
        do_action("after_save_post_{$post->post_type}", $object_id);
    }

    protected function isPostedEditLock($meta_key)
    {
        $action = $_POST['action'] ?? '';
        return $meta_key == '_edit_lock' && $action == 'editpost';
    }
}