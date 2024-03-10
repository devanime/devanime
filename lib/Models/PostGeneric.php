<?php
/**
 * Class Post_Generic
 * @package DevAnime\Models
 * @author  DevAnime
 * @version 1.0
 */

namespace DevAnime\Models;


class PostGeneric extends PostBase {

    protected function isValidPostInit()
    {
        return true;
    }
}