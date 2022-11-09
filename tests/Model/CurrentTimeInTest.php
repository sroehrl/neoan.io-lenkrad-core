<?php

namespace Test\Model;

use Neoan\Enums\Direction;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Transformers\CurrentTimeIn;
use PHPUnit\Framework\TestCase;

class CurrentTimeInTest extends TestCase
{

    public function test__invoke()
    {
        $transformer = new CurrentTimeIn();
        $result = $transformer(['date' => null], Direction::IN, 'date');
        $this->assertInstanceOf(DateTimeProperty::class, $result['date']);
    }
}
