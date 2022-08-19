<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class Initialize implements ModelAttribute
{
    private mixed $setter;

    public function __construct(mixed $setter)
    {
        $this->setter = $setter;
    }

    public function __Invoke(array $modelArray, Direction $direction, string $property): array
    {
        if ($direction === Direction::IN && empty($modelArray[$property])) {
            $modelArray[$property] = $this->setter;
        }

        return $modelArray;
    }

    public function getType(): AttributeType
    {
        return AttributeType::INITIAL;
    }
}