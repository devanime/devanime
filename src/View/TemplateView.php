<?php

namespace Backstage\View;

use Backstage\Models\ObjectBase;
use Backstage\Util;

abstract class TemplateView extends ObjectView implements View
{
    protected $parent_name;
    protected $default_template;
    protected static $base_path = '';

    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        if (empty($this->default_template)) {
            $this->setDefaultTemplate();
        }
    }

    public function getTemplateName(): string
    {
        $name = $this->getName();
        return $this->parent_name ? "$this->parent_name/$name" : $name;
    }

    protected function getTemplates(): array
    {
        $templates = (array) apply_filters('backstage/view/template/' . $this->getName(), [], $this);
        $templates = (array) apply_filters('backstage/view/template', $templates, $this);
        $templates[] = $this->default_template;
        return array_filter($templates);
    }

    protected function setDefaultTemplate()
    {
        $this->default_template = 'templates/' . $this->getName();
    }

    public static function getBasePath(): string
    {
        return static::$base_path;
    }

    public static function setBasePath(string $base_path)
    {
        static::$base_path = $base_path;
    }

    protected function render(array $scope): string
    {
        $templates = $this->getTemplates();
        if (empty($templates)) {
            error_log('Template for view '  . get_class($this) . ' is not set');
            return '';
        }
        $content = Util::getTemplateScoped($templates, $scope, static::getBasePath());
        $hook_name = $this->getName();
        ob_start();
        do_action('backstage/view/before_' . $hook_name, $this);
        echo (string) $content;
        do_action('backstage/view/after_' . $hook_name, $this);
        return ob_get_clean();
    }
}
