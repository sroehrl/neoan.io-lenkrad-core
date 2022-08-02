<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class IsForeignKey implements ModelAttribute
{
    public string $table;
    public string $property;
    public function __construct(string $table, string $property)
    {
        $this->table = $table;
        $this->property = $property;
    }

    public function getType(): AttributeType
    {
        return AttributeType::DECLARE;
    }
}