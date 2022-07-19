<?php
/**
 * Class ObjectBase
 * @package Backstage\Models
 * @author  ccollier
 * @version 2.0
 */

namespace Backstage\Models;

use Backstage\Util;

abstract class ObjectBase {

    function __construct($properties = []) {
        if ($properties && is_array($properties)) {
            $this->setProperties($properties);
        }
    }

    public function setProperties(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            if ($this->hasProperty($key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * General-purpose magic method to use dynamic accessors
     *
     * @param $name
     * @return mixed
     */
    public function __get($name) {
        $accessor_method = 'get' . Util::toPascalCase($name);
        if (method_exists($this, $accessor_method)) {
            return $this->$accessor_method();
        }
        return null;
    }

    /**
     * General-purpose magic method to use dynamic mutators
     *
     * @param $name
     * @param $value
     * @return mixed
     */
    public function __set($name, $value) {
        $mutator_method = 'set' . Util::toPascalCase($name);
        if (method_exists($this, $mutator_method)) {
            return $this->$mutator_method($value);
        }
        return null;
    }

    public function __isset($name)
    {
        return isset($this->$name);
    }

    protected function hasProperty($key): bool
    {
        return property_exists($this, $key);
    }

}