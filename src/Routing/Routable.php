<?php

namespace Neoan\Routing;

interface Routable
{
    public function __invoke(array $provided): mixed;
}