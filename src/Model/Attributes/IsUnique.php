<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class IsUnique implements ModelAttribute
{
    public function __construct(){}
    public function __invoke(){}

    public function getType(): AttributeType
    {
        return AttributeType::DECLARE;
    }
}