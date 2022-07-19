<?php

namespace Backstage\Support;

class NetworkCommand
{
    protected $callback;
    protected $arguments;
    protected $site_id;

    public function __construct(callable $callback, array $arguments = [], int $site_id = null)
    {
        $this->callback = $callback;
        $this->arguments = $arguments;
        if (!$site_id) {
            $site_id = get_main_site_id();
        }
        $this->site_id = $site_id;
    }

    public function execute()
    {
        $this->switchToSite();
        $return = call_user_func_array($this->callback, $this->arguments);
        $this->returnToCurrentSite();
        return $return;
    }

    private function switchToSite()
    {
        if (function_exists('switch_to_blog')) {
            switch_to_blog($this->site_id);
        }
    }

    private function returnToCurrentSite()
    {
        if (function_exists('restore_current_blog')) {
            restore_current_blog();
        }
    }

    public function __invoke()
    {
        $this->arguments = func_get_args();
        return $this->execute();
    }
}
