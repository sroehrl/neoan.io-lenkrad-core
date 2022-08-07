<?php

namespace Test\Model;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Helper\AttributeHelper;
use Neoan\Model\Attributes\HasMany;
use Neoan\Model\Attributes\Ignore;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Attributes\IsForeignKey;
use Neoan\Model\Attributes\Transform;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockModel;

class AttributeTest extends TestCase
{
    function testTransform()
    {
        $attribute = new Transform(NotImplemented::class);
        $this->expectException(\Exception::class);
        $attribute([],Direction::IN, '');
    }
    function testIgnore()
    {
        $attribute = new Ignore();
        $this->assertSame(AttributeType::PRIVATE, $attribute->getType());
    }
    function testInitialize()
    {
        $attribute = new Initialize('test');
        $result = $attribute(['test'=>null], Direction::IN, 'test');
        $this->assertSame($result['test'], 'test');
    }
    function testIsForeignKey()
    {
        $attribute = new IsForeignKey('test','test');
        $this->assertSame('test', $attribute->table);
        $this->assertSame(AttributeType::DECLARE, $attribute->getType());
    }
    function testHasMany()
    {
        $attribute = new HasMany(Implemented::class,['notId' => 'anotherId']);
        $result = $attribute(1, 'different');
        $this->assertSame('anotherId', $result['notId']);
    }
    function testFindAttributesByProperty()
    {
        $helper = new AttributeHelper(MockModel::class);
        $this->assertIsArray($helper->findAttributesByProperty('id'));
    }
}

class NotImplemented{}
class Implemented{
    static function retrieve($array): array
    {
        return $array;
    }
}


