<?php

namespace Backstage\Models;

/**
 * Class PostCollection
 * @package Backstage\Models
 * @author Cyrus Collier <ccollier@situationinteractive.com>
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
