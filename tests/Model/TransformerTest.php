<?php

namespace Test\Model;

use Neoan\Enums\Direction;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Transformers\CurrentTimeIn;
use Neoan\Model\Transformers\LockedTimeIn;
use PHPUnit\Framework\TestCase;

class TransformerTest extends TestCase
{

    public function testCurrentTimeIn()
    {
        $transformer = new CurrentTimeIn();
        $result = $transformer(['date' => null], Direction::IN, 'date');
        $this->assertInstanceOf(DateTimeProperty::class, $result['date']);
    }
    public function testLockedTimeIn()
    {
        $transformer = new LockedTimeIn();
        $res = $transformer(['date' => '2022-01-02'], Direction::OUT, 'date');
        $this->assertInstanceOf(DateTimeProperty::class, $res['date']);
    }
}
