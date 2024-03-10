<?php

namespace DevAnime\View;

abstract class ComponentView extends TemplateView
{
    protected function setDefaultTemplate()
    {
        $this->default_template = "templates/components/$this->name";
    }
}
