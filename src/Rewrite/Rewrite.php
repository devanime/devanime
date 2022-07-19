<?php

namespace DevAnime\Rewrite;

abstract class Rewrite
{
    protected $hook;

    protected $path;

    protected $arguments_count = 0;

    protected $callback;

    public function __construct($path)
    {
        $this->hook = 'route_' . md5($path);
        $this->path = $path;
        $this->setArgumentCount();
        add_action('wp_loaded', [$this, 'addRewriteRule']);
        add_filter('query_vars', [$this, 'setQueryVars']);
    }

    public function addRewriteRule()
    {
        add_rewrite_rule($this->path, 'index.php?' . $this->getQuery(), 'top');
    }

    public function setQueryVars($query_vars)
    {
        return $query_vars;
    }

    protected function setArgumentCount()
    {
        $this->arguments_count = (int) preg_match_all('/\(.*?\)/', $this->path);
    }

    protected function matchesRequest()
    {
        return get_query_var('route') == $this->hook;
    }

    protected function getArgumentName($index)
    {
        return 'route_arg_' . $index;
    }

    protected function getArgumentValue($index)
    {
        return get_query_var($this->getArgumentName($index));
    }

    abstract protected function getQuery();

}