<?php

namespace DevAnime\View;

/**
 * Trait ViewElementPropertiesTrait
 * @package DevAnime\View
 *
 * @property array $class_modifiers
 * @property array $element_attributes
 */
trait ViewElementPropertiesTrait
{
    static private $component_default_properties = ['class_modifiers' => [], 'element_attributes' => []];

    static protected $default_properties = [];

    /**
     * @param array|string $class_modifiers
     * @return $this
     */
    public function classModifiers($class_modifiers)
    {
        $this->mergeArrayProperty('class_modifiers', $class_modifiers);
        return $this;
    }

    /**
     * @param array|string $element_attributes
     * @return $this
     */
    public function elementAttributes($element_attributes)
    {
        $this->mergeArrayProperty('element_attributes', $element_attributes);
        return $this;
    }

    protected function mergeArrayProperty($property_name, $values)
    {
        if (!is_array($values)) {
            $values = [$values];
        }
        $this->{$property_name} = array_merge($this->{$property_name}, $values);
    }

    protected function mergeProperties($properties)
    {
        return array_merge(
            self::$component_default_properties,
            static::$default_properties,
            $properties
        );
    }

    protected static function isComponentDefaultProperty($key)
    {
        return isset(self::$component_default_properties[$key]);
    }
}