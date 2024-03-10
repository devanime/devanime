<?php

namespace DevAnime\Rewrite;

class RedirectRoute extends Route
{
    protected $redirect_url;

    public function __construct($path, callable $callback, $redirect_url = '/')
    {
        parent::__construct($path, $callback);
        $this->redirect_url = home_url($redirect_url);
    }

    public function processRoute()
    {
        $redirect = parent::processRoute();
        if (false !== $redirect) {
            wp_redirect($this->redirect_url);
            exit;
        }
    }

}