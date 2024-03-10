<?php

namespace DevAnime\View;

use DevAnime\Models\PostBase;
use DevAnime\View\Component;

/**
 * Class ItemComponent
 * @package DevAnime\View
 *
 * @property ParentComponent $parent
 */
class ItemComponent extends Component
{
    public function setParent(ParentComponent $View = null)
    {
        $this->setValue('parent', $View);
        return $this;
    }

    protected function setupRenderScope(array $scope): array
    {
        if ($this->parent instanceof ParentComponent) {
            foreach ($this->parent->getScope() as $key => $value) {
                if (
                    array_key_exists($key, $scope) && empty($scope[$key]) &&
                    !static::isComponentDefaultProperty($key)
                ) {
                    $scope[$key] = $value;
                }
            }
        }
        return $scope;
    }

    protected function mergeProperties($properties)
    {
        return array_merge(['parent' => null], parent::mergeProperties($properties));
    }
}
