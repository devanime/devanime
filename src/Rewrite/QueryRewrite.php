<?php

namespace Backstage\Rewrite;

class QueryRewrite extends Rewrite
{
    protected $query = [];

    public function __construct($path, $query = [])
    {
        parent::__construct($path);
        $this->query = $query;
    }

    public function setQueryVars($query_vars)
    {
        return array_merge($query_vars, array_keys($this->query));
    }

    protected function getQuery()
    {
        return preg_replace('/=\$(\d)/', '=$matches[$1]', build_query($this->query));
    }

}