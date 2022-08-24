<?php

namespace Test\Enums;

use Neoan\Enums\ResponseOutput;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    function testResponseOutput(){
        $this->assertSame('html', ResponseOutput::HTML->output());
        $this->assertSame('json', ResponseOutput::JSON->output());

    }
}
