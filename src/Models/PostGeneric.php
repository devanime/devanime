<?php
/**
 * Class Post_Generic
 * @package Backstage\Models
 * @author  ccollier
 * @version 1.0
 */

namespace Backstage\Models;


class PostGeneric extends PostBase {

    protected function isValidPostInit()
    {
        return true;
    }
}