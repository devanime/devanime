<?php

namespace Backstage\View;

use Backstage\Util;

class ListView implements View
{
    protected $tag = 'ul';
    protected $items;
    protected $attributes = [];

    /**
     * Creates a <ul> list
     *
     * Supported formats:
     * - Array of strings: ['Item 1', 'Item 2', 'Item 3']
     * - Array of ElementView objects:
     *      [new Element('Item 1', ['class' => 'list-item']), new Element('Item 2', ['class' => 'list-item'])]
     *
     * @param array $items
     * @param array $attributes
     */
    public function __construct(array $items, array $attributes = [])
    {
        $items = array_map([static::class, 'buildItem'], $items);
        $this->items = new ViewCollection($items);
        $this->attributes = $attributes;
    }

    /**
     * Creates a <ol> list
     *
     * @param array $items
     * @param array $attributes
     *
     * @return static
     */
    public static function ordered(array $items, array $attributes = [])
    {
        $list = new static($items, $attributes);
        $list->tag = 'ol';
        return $list;
    }

    /**
     * Creates a <dl>
     *
     * Supported formats:
     * - Assoc. array of term/description sets:
     *      ['Term 1' => 'Desc. 1', 'Term 2' => 'Desc. 2', 'Term 3' => 'Desc. 3']
     * - Array of Element pairs:
     *      [new Element('Item 1', ['class' => 'list-item']), new Element('Item 2', ['class' => 'list-item'])]
     *
     * @param array $items
     * @param array $attributes
     *
     * @return static
     */
    public static function definition(array $items, array $attributes = [])
    {

        $list = new static([], $attributes);
        $built_items = [];
        foreach ($items as $term => $desc) {
            if (is_array($desc)) {
                $term = $desc[0];
                $desc = $desc[1] ?? '';
            }
            $built_items[] = new ViewCollection([
                static::buildItem($term, 'dt'),
                static::buildItem($desc, 'dd')
            ]);
        }
        $list->items = new ViewCollection($built_items);
        $list->tag = 'dl';
        return $list;
    }

    public function getName(): string
    {
        return 'list';
    }

    public function getScope(): array
    {
        return get_object_vars($this);
    }

    public function __toString()
    {
        $content = PHP_EOL . $this->items . PHP_EOL;
        return Util::wrapElement($content, $this->tag, $this->attributes);
    }

    public function count()
    {
        return $this->items->count();
    }

    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    public function hasItems()
    {
        return $this->items->hasItems();
    }

    /**
     * @param $item
     * @param string $tag
     * @return Element
     */
    protected static function buildItem($item, $tag = 'li'): Element
    {
        if ($item instanceof Element) {
            if (!$item->hasTag()) {
                return $item->transform($tag);
            }
            if ($item->isTag($tag)) {
                return $item;
            }
        }
        return Element::create($tag, $item);
    }


}