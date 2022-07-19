<?php

namespace DevAnime\Models;

use ArrayIterator, InvalidArgumentException;
use DevAnime\Producers\RelatedContent\RelatedContent;

/**
 * Class ObjectCollection
 * @package DevAnime\Models
 * @author DevAnime <devanimecards@gmail.com>
 * @version 1.0
 */
abstract class ObjectCollection extends ImmutableCollection
{
    protected $items;
    protected $items_hashmap;
    protected static $object_class_name = null;

    public function __construct(array $items = [])
    {
        /**
         * Backwards compatibility
         */
        if (isset($this->object_class)) {
            static::$object_class_name = $this->object_class;
            unset($this->object_class);
            //trigger_error('The object_class property on ObjectCollection is now declared statically', E_USER_DEPRECATED);
        }
        $this->items = $this->items_hashmap = [];
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    public function add($item)
    {
        return $this()->addItem($item);
    }

    public function replace($offset, $item)
    {
        return $this()->replaceItem($offset, $item);
    }

    public function remove($item): self
    {
        return $this()->removeItem($item);
    }

    public function find($id)
    {
        $hash = $this->getHashFromId($id);
        return isset($this->items_hashmap[$hash]) ?
            $this->items[$this->items_hashmap[$hash]] : false;
    }

    public function walk(callable $callback)
    {
        $this->map($callback);
        return $this;
    }

    public function walkMethod(string $method_name, ...$method_args)
    {
        $this->mapMethod($method_name, ...$method_args);
        return $this;
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    public function mapMethod(string $method_name, ...$method_args): array
    {
        return $this->map(function ($item) use ($method_name, $method_args) {
            return $this->callMethodChain($item, $method_name, $method_args);
        });
    }

    public function filter(callable $callback)
    {
        $that = $this();
        foreach ($that->items as $item) {
            if (!$callback($item)) {
                $that->removeItem($item);
            }
        }
        return $that;
    }

    public function filterMethod(string $method_name, ...$method_args)
    {
        $that = $this();
        foreach ($that->items as $item) {
            if (!$that->callMethodChain($item, $method_name, $method_args)) {
                $that->removeItem($item);
            }
        }
        return $that;
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function reduceMethod(string $method_name, ...$args)
    {
        $initial = array_shift($args);
        return $this->reduce(function($carry, $item) use ($method_name, $args) {
            array_unshift($args, $carry);
            return $this->callMethodChain($item, $method_name, $args);
        }, $initial);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->getAll());
    }

    public function getAll(): array
    {
        return $this->items;
    }

    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function count()
    {
        return count($this->items);
    }

    public function isEmpty()
    {
        return $this->count() == 0;
    }

    public function hasItems()
    {
        return !$this->isEmpty();
    }

    public function first()
    {
        return $this->items[0] ?? null;
    }

    public function last()
    {
        return $this->items[$this->count() - 1] ?? null;
    }

    protected function addItem($item)
    {
        $this->validateClass($item);
        if (!$this->hashExists($item)) {
            $this->items[] = $item;
            end($this->items);
            $this->items_hashmap[$this->getObjectHash($item)] = key($this->items);
        }
        return $this;
    }

    protected function replaceItem($offset, $item)
    {
        $this->validateClass($item);
        if (!$this->hashExists($item)) {
            $this->items[(int)$offset] = $item;
            $this->items_hashmap[$this->getObjectHash($item)] = (int)$offset;
        }
        return $this;
    }

    protected function removeItem($item)
    {
        $this->validateClass($item);
        $index = $this->getHashIndex($item);
        if (false !== $index) {
            $item = $this->items[(int)$index];
            unset($this->items_hashmap[$this->getObjectHash($item)]);
            unset($this->items[(int)$index]);
        }
        return $this;
    }

    protected function getHashFromId($id)
    {
        return md5($id);
    }

    protected function validateClass($item)
    {
        if (!$item instanceof static::$object_class_name) {
            throw new InvalidArgumentException('Object passed to collection is not a ' . static::$object_class_name);
        }
    }

    protected function hashExists($item)
    {
        return isset($this->items_hashmap[$this->getObjectHash($item)]);
    }

    protected function getHashIndex($item) {
        return $this->items_hashmap[$this->getObjectHash($item)] ?? false;
    }

    protected function callMethodChain($item, $method_chain, $method_args)
    {
        $chain = array_reverse(explode('.', $method_chain));
        while (!empty($chain)) {
            $method_name = array_pop($chain);
            $item = call_user_func_array([$item, $method_name], count($chain) ? [] : $method_args);
        }
        return $item;
    }

    public function __invoke()
    {
        return clone $this;
    }

    abstract protected function getObjectHash($item);

}
