<?php

namespace Neoan\Model\Attributes;

use Attribute;
use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Model\Interfaces\ModelAttribute;

#[Attribute]
class IsEnum implements ModelAttribute
{
    public string $enum;

    public function __construct(string $backedEnum)
    {
        $this->enum = $backedEnum;
    }

    public function __invoke(array $result, Direction $direction, string $property): array
    {

        if($direction === Direction::OUT) {
            $result[$property] = $this->enum::from($result[$property]);
        }
        return $result;
    }

    public function getType(): AttributeType
    {
        return AttributeType::MUTATE;
    }
}