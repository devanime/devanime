<?php
/**
 * Created by PhpStorm.
 * User: ccollier
 * Date: 9/30/17
 * Time: 5:42 AM
 */

namespace Backstage\Models;


abstract class ImmutableCollection implements Collection {

    use ImmutableCollectionTrait;
}