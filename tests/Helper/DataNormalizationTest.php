<?php

namespace Test\Helper;

use Neoan\Helper\DataNormalization;
use Neoan\Model\Collection;
use Neoan\Store\Store;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockModel;

class DataNormalizationTest extends TestCase
{
    function testDataNormalizationHelper()
    {
        Store::write('wednesdays','wednesdays');
        $helper = new DataNormalization([
            'model' =>new MockModel(['id'=>5]),
            'collection' =>new Collection(),
            'store' => Store::dynamic('wednesdays'),
            'key' => 'value'
        ]);
        $this->assertSame(5, $helper->converted['model']['id']);
        $this->assertSame('wednesdays', $helper->converted['store']);
        foreach ($helper as $key => $value){
            $this->assertTrue(in_array($key,['model','collection','store','key']));
        }
    }
    function testNormalize()
    {
        $res = DataNormalization::normalize(['key' => 'value']);
        $this->assertSame('value', $res->converted['key']);
    }
    function testMockInstance()
    {
        $mock = new DataNormalization(['key' => 'value']);
        $res = DataNormalization::getInstance($mock);
        $this->assertSame('value', $res->converted['key']);
    }
}
