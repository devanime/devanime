<?php

namespace Backstage\View;

use ReflectionClass, Throwable;

/**
 * Class Component
 *
 * @property array $class_modifiers
 * @property array $element_attributes
 *
 * @package Backstage\View
 */
abstract class Component extends TemplateView implements ViewElementProperties
{
    use ViewElementPropertiesTrait;

    static private $component_dirs;

    public function __construct(array $properties = [])
    {
        parent::__construct($this->mergeProperties($properties));
    }

    protected function setDefaultTemplate()
    {
        $class_name = get_class($this);
        if (empty(self::$component_dirs[$class_name])) {
            try {
                $reflector = new ReflectionClass($class_name);
                self::$component_dirs[$class_name] = dirname($reflector->getFileName());
            } catch (Throwable $e) {
                error_log($e->getMessage());
                return;
            }
        }
        $this->default_template = sprintf('%s/%s', self::$component_dirs[$class_name], $this->getName());
    }
}
