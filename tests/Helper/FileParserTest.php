<?php

namespace Test\Helper;

use Neoan\Helper\FileParser;
use PHPUnit\Framework\TestCase;

class FileParserTest extends TestCase
{

    function testJs()
    {
        $this->testExtension('js');
    }
    function testCss()
    {
        $this->testExtension('css');
    }
    function testSvg()
    {
        $this->testExtension('svg');
    }
    function testJpg()
    {
        $this->testExtension('jpg');
    }
    private function testExtension($extension)
    {
        $this->expectExceptionMessage('Wanted to exit');
        $n = new FileParser(dirname(__DIR__). '/Mocks/mockFiles/mock' . '.' . $extension);
    }
}
