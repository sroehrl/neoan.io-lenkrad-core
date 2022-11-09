<?php

namespace Neoan\Model\Transformers;

use Neoan\Enums\Direction;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Interfaces\Transformation;

class LockedTimeIn implements Transformation
{

    public function __invoke(array $inputOutput, Direction $direction, string $property): array
    {
        if($direction === Direction::OUT && isset($inputOutput[$property])) {
            $inputOutput[$property] = new DateTimeProperty($inputOutput[$property]);
        } elseif ($direction === Direction::IN && $inputOutput[$property] instanceof DateTimeProperty && !$inputOutput[$property]->value) {
            $inputOutput[$property] = null;
        }

        return $inputOutput;
    }
}