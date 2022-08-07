<?php

namespace Test\Errors;

use Neoan\Errors\NotFound;
use Neoan\Errors\Unauthorized;
use PHPUnit\Framework\TestCase;

class ErrorTest extends TestCase
{
    function test404()
    {
        NotFound::setTemplate(dirname(__DIR__). '/Request/test.txt');
        $this->expectErrorMessage('Wanted to die');
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        new NotFound('not here');
    }
    function testUnauthorized()
    {
        $this->expectErrorMessage('Wanted to die');
        $this->setOutputCallback(function($output){
            var_dump($output);
        });
        new Unauthorized();
    }
}
