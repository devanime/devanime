<?php

namespace Backstage\Repositories;

use Backstage\Models\Field;
use Backstage\Models\PostBase;

interface ImmutableRepository {

    function findById($id);

    function findOne(array $query);

    function findAll();

    function find(array $query);

}