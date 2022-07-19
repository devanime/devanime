<?php

namespace DevAnime\Models;

abstract class FieldCollection implements Field
{
    abstract protected function getFields();

    public function getValue()
    {
        $fields = $this->getFields();
        foreach ($fields as &$value) {
            if ($value instanceof Field) {
                $value = $value->getValue();
            }
        }
        return $fields;
    }
}