<?php

namespace DevAnime\Repositories;

use DevAnime\Models\Field;
use DevAnime\Models\PostBase;

interface ImmutableRepository {

    function findById($id);

    function findOne(array $query);

    function findAll();

    function find(array $query);

}