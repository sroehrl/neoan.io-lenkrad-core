<?php

namespace Test\Helper;

use Neoan\Helper\DateHelper;
use PHPUnit\Framework\TestCase;

class DateHelperTest extends TestCase
{

    public function test__toString()
    {
        $d = new DateHelper();
        $this->assertMatchesRegularExpression($this->getDatePattern(), (string)$d);
    }
    public function testParseDateInput()
    {
        $d = new DateHelper(time());
        $this->assertMatchesRegularExpression($this->getDatePattern(), (string)$d);
    }
    private function getDatePattern()
    {
        return '/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/';
    }
}
