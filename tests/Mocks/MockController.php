<?php

namespace Test\Mocks;

use Neoan\Routing\Attributes\Web;

#[Web('/', '/index.html')]
class MockController implements \Neoan\Routing\Routable
{

    public function __invoke(array $provided): array
    {
        return ['call' => 'me'];
    }
}