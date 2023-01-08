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

    public function __construct(string $modelName = null, string $property = null)
    {
        $this->property = $property;
        $this->modelName = $modelName;
    }

    public function getType(): AttributeType
    {
        return AttributeType::DECLARE;
    }
}