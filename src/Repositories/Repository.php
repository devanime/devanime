<?php

namespace Backstage\Repositories;

use Backstage\Models\Field;
use Backstage\Models\PostBase;

interface Repository extends ImmutableRepository {

    function add($object);

    function remove($object);

}