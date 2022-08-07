<?php

namespace Neoan\Model\Attributes;
use Attribute;
use Exception;
use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Helper\AttributeHelper;
use Neoan\Model\Interfaces\ModelAttribute;
use Neoan\Model\Interfaces\Transformation;
use ReflectionException;

#[Attribute]
class Transform implements ModelAttribute
{
    private string $converterClass;
    public function __construct(string $converterClass){
        $this->converterClass = $converterClass;

    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    public function __invoke(array $result, Direction $direction, string $property){
        $check = new AttributeHelper($this->converterClass);
        if(!$check->reflection->implementsInterface(Transformation::class)){
            throw new Exception($this->converterClass . ' does not implement ' . Transformation::class);
        }
        $class = new $this->converterClass();
        return $class($result, $direction, $property);
    }

    public function getType(): AttributeType
    {
        return AttributeType::MUTATE;
    }
}