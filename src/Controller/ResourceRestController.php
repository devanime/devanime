<?php

namespace Backstage\Controller;

use WP_REST_Request;

abstract class ResourceRestController extends RestController
{
    protected $resource_path = '/';

    public function registerRoutes() {
        $this->addRoutes($this->resource_path, [
            $this->addReadAction('list')
        ]);
        $this->addRoutes($this->resource_path . '/(?P<id>\d+)', [
            $this->addCreateAction('create'),
            $this->addReadAction('read'),
            $this->addEditAction('update'),
            $this->addDeleteAction('delete')
        ]);
    }

    abstract public function list(WP_REST_Request $request);

    abstract public function create(WP_REST_Request $request);

    abstract public function read(WP_REST_Request $request);

    abstract public function update(WP_REST_Request $request);

    abstract public function delete(WP_REST_Request $request);
}