<?php

namespace DevAnime\View;

use DevAnime\Models\Collection;
use DevAnime\Models\ObjectCollection;
use DevAnime\Util;

/**
 * Class WrapperComponent
 * @package DevAnime\View
 *
 * @property ItemComponentCollection $items
 */
abstract class ParentComponent extends ObjectView
{
    use ViewElementPropertiesTrait;

    protected $wrapper_tag = 'div';

    public function __construct(array $properties = [])
    {
        parent::__construct(array_merge(['items' => null], $this->mergeProperties($properties)));
    }

    protected function setItems($items)
    {
        if (!$items instanceof ItemComponentCollection) {
            if ($items instanceof ObjectCollection) {
                $items = $items->getAll();
            }
            $items = new ItemComponentCollection(array_filter((array) $items));
        }
        $items->walkMethod('setParent', $this);
        $this->setValue('items', $items);
        return $this;
    }

    function render(array $scope): string
    {
        return (string) Element::create($this->wrapper_tag, $scope['items'], Util::componentAttributesArray(
            $this->name, $scope['class_modifiers'], $scope['element_attributes']
        ));
    }
}
