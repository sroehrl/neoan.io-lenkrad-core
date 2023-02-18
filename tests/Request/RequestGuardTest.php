<?php

namespace Test\Request;

use Neoan\Model\Helper\DateTimeProperty;
use Test\Mocks\MockRequestGuard;
use Neoan\Request\Request;
use PHPUnit\Framework\TestCase;

class RequestGuardTest extends TestCase
{
    function testSuccessAllowedIncomplete()
    {
        Request::setParameter('fill', 'filled');
        Request::setParameter('castToInt', '1');
        Request::setParameter('type', 'mutate');
        $request = (new MockRequestGuard())();
        $this->assertIsInt($request->castToInt);
        $this->assertSame('filled', $request->fill);
    }
    function testSuccessCustomType()
    {
        Request::setParameter('fill', 'filled');
        Request::setParameter('castToInt', '1');
        Request::setParameter('createdAt', '2023-01-01');
        Request::setParameter('type', 'mutate');
        $request = (new MockRequestGuard())();
        $this->assertInstanceOf(DateTimeProperty::class, $request->createdAt);
    }
    function testFailure()
    {
        $this->expectErrorMessage('Wanted to die');
        Request::setParameters(['fill' => null]);
        $request = (new MockRequestGuard())();
    }
}

