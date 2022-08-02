<?php

namespace Neoan\Model\Attributes;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Model\ModelAttribute;

#[\Attribute]
class Ignore extends ModelAttribute
{

    function __invoke(array $result, Direction $direction, string $property)
    {
        unset($result[$property]);
        return $result;
    }

    public function getType(): AttributeType
    {
        return AttributeType::MUTATE;
    }
}