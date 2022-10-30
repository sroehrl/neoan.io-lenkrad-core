<?php

namespace Neoan\Routing\Interfaces;

use Neoan\Provider\Interfaces\Provide;

interface Routable
{
    public function __invoke(Provide $provided): mixed;
}