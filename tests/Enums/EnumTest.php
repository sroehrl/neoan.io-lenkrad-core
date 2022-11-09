<?php

namespace Test\Enums;

use Neoan\Enums\ResponseOutput;
use Neoan\Enums\TimePeriod;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    function testResponseOutput(){
        $this->assertSame('html', ResponseOutput::HTML->output());
        $this->assertSame('json', ResponseOutput::JSON->output());

    }
    function testTimePeriod()
    {
        $inTwoMinutes = new \DateInterval(TimePeriod::SECONDS->getPeriod(2));
        $this->assertSame( '2', $inTwoMinutes->format('%s'));
        $aDay = new \DateInterval(TimePeriod::DAYS->getPeriod(1));
        $this->assertSame( '1', $aDay->format('%d'));
    }
}
