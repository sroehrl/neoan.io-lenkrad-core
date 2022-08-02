<?php

namespace Neoan\Model\Attributes;

use Neoan\Enums\AttributeType;
use Neoan\Enums\Direction;
use Neoan\Model\ModelAttribute;

#[\Attribute]
class Initialize extends ModelAttribute
{
    public AttributeType $type = AttributeType::INITIAL;
    private mixed $setter;
    public function __construct(mixed $setter)
    {
        $this->setter = $setter;
    }
    public function __Invoke(array $modelArray, Direction $direction, string $property): array
    {
        if($direction === Direction::IN && empty($modelArray[$property])){
            $modelArray[$property] = $this->setter;
        }

        return $modelArray;
    }

}