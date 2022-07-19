<?php

namespace DevAnime\Repositories;

use DevAnime\Models\Field;
use DevAnime\Models\PostBase;

interface Repository extends ImmutableRepository {

    function add($object);

    function remove($object);

}