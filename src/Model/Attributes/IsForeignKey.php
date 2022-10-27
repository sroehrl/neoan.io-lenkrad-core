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
    public ?string $modelName;

    public function __construct(string $table, string $property, string $modelName = null)
    {
        $this->table = $table;
        $this->property = $property;
        $this->modelName = $modelName;
    }

    public function getType(): AttributeType
    {
        return AttributeType::DECLARE;
    }
}