<?php

namespace Test\Mocks;

use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Request\RequestGuard;

class MockRequestGuard extends RequestGuard
{
    public string $fill;

    public int $castToInt;

    public ?DateTimeProperty $createdAt;
}