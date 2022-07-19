<?php


namespace DevAnime\View;


interface ViewElementProperties extends View
{
    /**
     * @param array|string $class_modifiers
     * @return $this
     */
    public function classModifiers($class_modifiers);

    /**
     * @param array|string $element_attributes
     * @return $this
     */
    public function elementAttributes($element_attributes);

}