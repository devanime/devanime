<?php
/**
 * Created by PhpStorm.
 * User: DevAnime
 * Date: 9/30/17
 * Time: 5:42 AM
 */

namespace DevAnime\Models;


abstract class ImmutableCollection implements Collection {

    use ImmutableCollectionTrait;
}