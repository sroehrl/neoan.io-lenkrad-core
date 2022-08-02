<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class Ignore implements ModelAttribute
{

    function __invoke(array $result, Direction $direction, string $property)
    {
        unset($result[$property]);
        return $result;
    }

    public function getType(): AttributeType
    {
        return AttributeType::ATTACH;
    }
}