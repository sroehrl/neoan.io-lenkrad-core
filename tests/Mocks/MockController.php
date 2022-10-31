<?php

namespace Test\Mocks;

use Neoan\Provider\Interfaces\Provide;
use Neoan\Routing\Attributes\Web;

#[Web('/', '/index.html')]
class MockController implements \Neoan\Routing\Interfaces\Routable
{

    public function __invoke(Provide $provided): array
    {
        return ['call' => 'me'];
    }
}