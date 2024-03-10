<?php

namespace DevAnime\View;

use DevAnime\Models\ObjectBase;
use DevAnime\Util;

abstract class ObjectView extends ObjectBase implements View
{
    protected $name;
    private $data = [];

    public function __construct(array $properties = [])
    {
        parent::__construct($properties);
        if (empty($this->getName())) {
            throw new \RuntimeException('A name must be set for view ' . get_class($this));
        }
    }

    public function __toString()
    {
        try {
            $data = $this->setupRenderScope($this->data);
            return $this->render($data);
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            error_log(wp_debug_backtrace_summary());
        }
        return '';
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getScope(): array
    {
        return $this->data;
    }

    protected function hasProperty($key): bool
    {
        return true;
    }

    protected function setupRenderScope(array $scope): array
    {
        return $scope;
    }

    public function __get($name)
    {
        if ($value = parent::__get($name)) {
            return $value;
        }
        return $this->getValue($name);
    }

    public function __set($name, $value)
    {
        if ($result = parent::__set($name, $value)) {
            return $result;
        }
        $this->setValue($name, $value);
        return true;
    }

    public function __isset($property)
    {
        return isset($this->data[$property]);
    }

    protected function setValue($name, $value)
    {
        $this->data[$name] = $value;
    }

    protected function getValue($name)
    {
        return $this->data[$name] ?? null;
    }

    public static function registerShortcode()
    {
        $view = new static;
        add_shortcode($view->getName(), function ($attributes, $content = '') use ($view) {
            $view->setProperties(array_merge($attributes ?: [], compact('content')));
            return (string) $view;
        });
        if (isset($view->ui) && function_exists('shortcode_ui_register_for_shortcode')) {
            add_action('register_shortcode_ui', function () use ($view) {
                shortcode_ui_register_for_shortcode($view->getName(), $view->ui);
            });
        }
        return $view;
    }

    abstract protected function render(array $scope): string;
}
