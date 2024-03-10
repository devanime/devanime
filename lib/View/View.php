<?php

namespace DevAnime\View;

interface View
{
    function __toString();

    function getName(): string;

    function getScope(): array;
}
