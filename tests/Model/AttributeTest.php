<?php

namespace Test\Model;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Enums\TimePeriod;
use Neoan\Helper\AttributeHelper;
use Neoan\Helper\DateHelper;
use Neoan\Model\Attributes\HasMany;
use Neoan\Model\Attributes\Ignore;
use Neoan\Model\Attributes\Initialize;
use Neoan\Model\Attributes\IsEnum;
use Neoan\Model\Attributes\IsForeignKey;
use Neoan\Model\Attributes\Transform;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Traits\Setter;
use PHPUnit\Framework\TestCase;
use Test\Mocks\MockModel;

class AttributeTest extends TestCase
{
    use Setter;
    private string $testVariable;
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
    function testTraits()
    {
        $this->set('testVariable', 'works');
        $this->assertSame('works', $this->testVariable);
    }
    function testDateTimeProperty()
    {
        $attribute = new DateTimeProperty('now');

        $attribute->toNull();
        $this->assertNull($attribute->value);
        $this->assertSame('', (string) $attribute);

        $attribute->set('now');
        $twoMin = $attribute->getTimeDifference(new DateHelper('+2 minute'))->format('%i');
        $this->assertSame('2', $twoMin);

        $attribute->addTimePeriod(5, TimePeriod::DAYS);
        $null = $attribute->getTimeDifference(new DateHelper('+5 day'))->format('%d');
        $this->assertSame('0', $null);

        $this->assertStringStartsWith(date('Y'), (string) $attribute);

        $attribute->subtractTimePeriod(5, TimePeriod::DAYS);
        $five = $attribute->getTimeDifference(new DateHelper('+5 day'))->format('%d');
        $this->assertSame('5', $five);

        $attribute = new DateTimeProperty();
        $attribute->addTimePeriod(2, TimePeriod::DAYS);
        $this->expectErrorMessage('Wanted to die');
        $attribute->set('not a valid time');

    }
    function testIsEnum()
    {
        $attribute = new IsEnum(AttributeType::class);
        $result = $attribute(['test' => 'mutate'], Direction::OUT , 'test');
        $this->assertSame(AttributeType::MUTATE, $result['test']);
        $this->assertSame(AttributeType::MUTATE, $attribute->getType());

    }
}

class NotImplemented{}
class Implemented{
    static function retrieve($array): array
    {
        return $array;
    }
}


