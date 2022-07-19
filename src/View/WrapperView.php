<?php

namespace Backstage\View;

use Backstage\Util;

abstract class WrapperView implements View
{
    use ViewElementPropertiesTrait;

    protected $name;
    protected $wrap_container = false;
    protected $class_modifiers = [];
    protected $element_attributes = [];

    /**
     * @var Element
     */
    protected $Container;
    /**
     * @var View
     */
    protected $View;

    protected function setContainer(Element $Container)
    {
        $this->Container = $Container;
    }

    protected function setView(View $View)
    {
        $this->View = $View;
    }

    function __toString()
    {
        if (!$this->wrap_container) {
            return (string) $this->View;
        }
        if (empty($this->Container)) {
            $container = Element::create('div', '', Util::componentAttributesArray(
                $this->name, $this->class_modifiers, $this->element_attributes
            ));
            $this->setContainer($container);
        }
        return (string) $this->Container->content($this->View);
    }

    function getName(): string
    {
        return $this->name;
    }

    function getScope(): array
    {
        return $this->View->getScope();
    }

}
