<?php
/**
 * Created by PhpStorm.
 * User: ccollier
 * Date: 9/30/17
 * Time: 5:42 AM
 */

namespace Backstage\Models;


trait ImmutableCollectionTrait {

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Value setting is not allowed for an Immutable Collection');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Value un-setting is not allowed for an Immutable Collection');
    }
}