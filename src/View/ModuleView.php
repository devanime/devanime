<?php

namespace Backstage\View;

abstract class ModuleView extends TemplateView
{
    protected function setDefaultTemplate()
    {
        $this->default_template = "templates/modules/$this->name";
    }
}
