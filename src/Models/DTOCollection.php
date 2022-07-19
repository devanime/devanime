<?php

namespace Backstage\Models;

abstract class DTOCollection extends ObjectCollection implements \JsonSerializable
{
    protected static $object_class_name = DTO::class;

    public function __construct(iterable $items = [])
    {
        $dto_items = [];
        foreach ($items as $item) {
            if ($dto = $this->getDTO($item)) {
                $dto_items[] = $dto;
            }
        }
        parent::__construct($dto_items);
    }

    public function jsonSerialize()
    {
        return $this->getAll();
    }

    public function getItems(): array
    {
        return $this->getAll();
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    protected function getDTO($item): DTO
    {
        if (! $item instanceof DTO) {
            $item = $this->createDTO($item);
        }
        return $item;
    }

    protected function getObjectHash($item)
    {
        return md5($item);
    }


    /**
     * @param object $item
     * @return DTO
     */
    abstract protected function createDTO($item): DTO;
}
