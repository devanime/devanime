<?php

namespace Backstage\View;

use Backstage\Util;

class Element implements View
{
    protected $tag = '';
    protected $content = '';
    protected $attributes = [];

    public function __construct(string $content = '', array $attributes = [])
    {
        $this->content = $content;
        $this->attributes = $attributes;
    }

    /**
     * @param string $tag - only used if not already set on instance
     * @param string $content
     * @param array $attributes
     * @return static
     */
    public static function create(string $tag, $content = '', array $attributes = [])
    {
        $element = new self($content, $attributes);
        if ($tag && !$element->tag) {
            $element->tag = $tag;
        }
        return $element;
    }

    public function getName(): string
    {
        return $this->tag;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getScope(): array
    {
        return get_object_vars($this);
    }

    public function __toString()
    {
        return $this->tag ? Util::wrapElement($this->content, $this->tag, $this->attributes) : $this->content;
    }

    public function hasTag()
    {
        return !empty($this->getName());
    }

    public function isTag($tag)
    {
        return $tag == $this->getName();
    }

    /**
     * @param string $tag
     * @return Element - The NEW instance with all the original properties
     */
    public function transform(string $tag)
    {
        return static::create($tag, $this->content, $this->attributes);
    }

    /**
     * @param string $content
     * @return Element - The NEW instance with the given content
     */
    public function content(string $content = ''): self
    {
        $element = clone $this;
        $element->content = $content;
        return $element;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Element - The NEW instance with the given attribute
     */
    public function attribute(string $key, $value = ''): self
    {
        return $this->attributes([$key => $value]);
    }

    /**
     * @param array $attributes
     * @return Element - The NEW instance with the given attributes
     */
    public function attributes(array $attributes = []): self
    {
        $element = clone $this;
        $element->attributes = array_merge($element->attributes, $attributes);
        return $element;
    }

    public function addClass($class_to_add) {
        $classes = $this->getNormalizedClass();
        $classes[] = $class_to_add;
        return $this->attribute('class', array_unique($classes));
    }

    public function removeClass($class_to_remove) {
        $classes = $this->getNormalizedClass();
        $classes_map = array_combine($classes, $classes);
        if (isset($classes_map[$class_to_remove])) {
            unset($classes_map[$class_to_remove]);
        }
        return $this->attribute('class', array_unique(array_values($classes_map)));
    }

    /**
     * @param $name - Attribute name
     * @param $arguments - Attribute value
     * @return Element - The NEW instance
     */
    public function __call($name, $arguments): self
    {
        return $this->attribute($name, $arguments[0]);
    }

    /**
     * @param $content
     * @param array $attributes
     * @return Element - The NEW instance with the given content and optional attributes
     */
    public function __invoke(string $content = '', $attributes = [])
    {
        $element = $this->content($content);
        if (!empty($attributes)) {
            $element = $element->attributes($attributes);
        }
        return $element;
    }

    protected function getNormalizedClass()
    {
        $classes = $this->attributes['class'] ?? [];
        if (!is_array($classes)) {
            $classes = explode(' ', $classes);
        }
        return $classes;
    }

}