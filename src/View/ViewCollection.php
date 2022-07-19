<?php

namespace Backstage\View;

use Backstage\Models\ObjectCollection;

class ViewCollection extends ObjectCollection implements View
{
    protected static $object_class_name = View::class;

    protected function getObjectHash($item)
    {
        return spl_object_hash($item);
    }

    public function __toString()
    {
        return implode(PHP_EOL, $this->items);
    }

    public function getName(): string
    {
        $base = !empty($this->items) ? $this->items[0]->getName() : 'view';
        return "$base-collection";
    }

    public function getScope(): array
    {
        return $this->mapMethod('getScope');
    }
}