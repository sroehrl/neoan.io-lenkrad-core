<?php

namespace Test\Request;

use Neoan\Model\Helper\DateTimeProperty;
use Test\Mocks\MockRequestGuard;
use Neoan\Request\Request;
use PHPUnit\Framework\TestCase;

class RequestGuardTest extends TestCase
{
    private $mockFile;
    protected function setUp(): void
    {
        $actual = dirname(__DIR__) . '/Mocks/index.html';
        $this->mockFile = [
            'name' => 'index.html',
            'type' => 'text/html',
            'size' => filesize($actual),
            'tmp_name' => $actual
        ];
    }

    function testSuccessAllowedIncomplete()
    {
        Request::setParameter('fill', 'filled');
        Request::setParameter('castToInt', '1');
        Request::setParameter('type', 'mutate');
        Request::setFiles(['file' =>  $this->mockFile]);
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
        Request::setFiles(['file' =>  $this->mockFile]);
        $request = (new MockRequestGuard())();
        $this->assertInstanceOf(DateTimeProperty::class, $request->createdAt);
    }
    function testFileUpload()
    {
        Request::setParameter('fill', 'filled');
        Request::setParameter('castToInt', '1');
        Request::setParameter('createdAt', '2023-01-01');
        Request::setParameter('type', 'mutate');
        Request::setFiles(['file' =>  $this->mockFile]);
        $request = (new MockRequestGuard())();
        $bytes = $request->file->size;
        $kb = $request->file->getSize();

        $this->assertSame($bytes/1024,$kb);
        $this->assertSame('html', $request->file->getExtension());

    }
    function testFailure()
    {
        $this->expectErrorMessage('Wanted to die');
        Request::setParameters(['fill' => null]);
        $request = (new MockRequestGuard())();
    }
}

