<?php
/**
 * Class Config_Loader
 * @package Backstage
 * @author  Jeremy Strom <jeremy.strom@gmail.com>
 * @version 1.0
 */

namespace Backstage;

use Backstage\Models\PostFactory;
use Backstage\Models\TermFactory;

class FactoryLoader {
    protected $post_type_models = [];
    protected $taxonomy_models = [];
    protected $data;

    function __construct() {
        add_action('init', [$this, 'initFactories'], 7);
    }

    public function initFactories() {
        $this->post_type_models = array_values(array_filter(apply_filters('backstage/register_post_type_models', [])));
        foreach ($this->post_type_models as $model_class) {
            PostFactory::registerPostModel($model_class);
        }
        $this->taxonomy_models = array_values(array_filter(apply_filters('backstage/register_taxonomy_models', [])));
        foreach ($this->taxonomy_models as $model_class) {
            TermFactory::registerTermModel($model_class);
        }
    }

}