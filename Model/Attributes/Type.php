<?php

namespace Neoan\Model\Attributes;
use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class Type implements ModelAttribute
{
    public string $propertyType;
    public ?string $propertyLength = null;
    public ?string $default = null;
    public function __construct(string $propertyType, int $propertyLength = null, string $default = null)
    {
        $this->propertyType = $propertyType;
        $this->propertyLength = $propertyLength;
        $this->default = $default;
    }

    public function getType(): AttributeType
    {
        return AttributeType::DECLARE;
    }
}