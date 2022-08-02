<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\ModelAttribute;

#[Attribute]
class IsForeignKey extends ModelAttribute
{
    public AttributeType $type = AttributeType::DECLARE;
    public string $table;
    public string $property;
    public function __construct(string $table, string $property)
    {
        $this->table = $table;
        $this->property = $property;
    }
}