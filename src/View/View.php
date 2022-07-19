<?php

namespace Backstage\View;

interface View
{
    function __toString();

    function getName(): string;

    function getScope(): array;
}
