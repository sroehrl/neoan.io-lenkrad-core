<?php

namespace Neoan\Model\Attributes;
use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\ModelAttribute;

#[Attribute]
class Type extends ModelAttribute
{
    public AttributeType $type = AttributeType::DECLARE;
    public string $propertyType;
    public ?string $propertyLength = null;
    public ?string $default = null;
    public function __construct(string $propertyType, int $propertyLength = null, string $default = null)
    {
        $this->propertyType = $propertyType;
        $this->propertyLength = $propertyLength;
        $this->default = $default;
    }
}