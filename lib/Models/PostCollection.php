<?php

namespace DevAnime\Models;

/**
 * Class PostCollection
 * @package DevAnime\Models
 * @author DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */
class PostCollection extends ObjectCollection
{
    protected static $object_class_name = PostBase::class;

    protected function getObjectHash($item)
    {
        return md5($item->ID ?: serialize($item));
    }

}
