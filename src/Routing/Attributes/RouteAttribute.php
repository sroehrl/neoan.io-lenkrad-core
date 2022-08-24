<?php

namespace Neoan\Routing\Attributes;

interface RouteAttribute
{
    function setControllerClass(string $qualifiedName);
    function generateRoute(): void;
}