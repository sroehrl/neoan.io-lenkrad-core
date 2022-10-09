<?php

namespace Test\Store;

use Neoan\Store\Store;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    function testToString()
    {
        $array = ['test' => 'me'];
        $ref = Store::dynamic('ref');
        Store::write('ref',$array);
        $this->assertSame(json_encode($array), (string) $ref);
    }
}
