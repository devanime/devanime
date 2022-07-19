<?php

namespace DevAnime\Html;

class LoadStack
{
    public function __construct()
    {
        if (is_admin()) {
            return;
        }
        add_action('wp_head', [$this, 'init'], 999);
        add_action('wp_footer', [$this, 'execute'], 999);
        add_action('init', function() {
            do_action('devanime/load_stack');
        });
    }

    public function init()
    {
        echo '<script>window.loadStack=window.loadStack||[];window.executeLoadStack=function(s){while(s.length){(s.shift())(jQuery);}}</script>';
    }

    public function execute()
    {
        echo '<script>window.executeLoadStack(window.loadStack)</script>';
    }
}
