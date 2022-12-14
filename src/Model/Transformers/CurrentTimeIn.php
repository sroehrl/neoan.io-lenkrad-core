<?php

namespace Neoan\Model\Transformers;

use Neoan\Enums\Direction;
use Neoan\Model\Helper\DateTimeProperty;
use Neoan\Model\Interfaces\Transformation;

class CurrentTimeIn implements Transformation
{

    public function __invoke(array $inputOutput, Direction $direction, string $property): array
    {
        if ($direction === Direction::IN) {
            $inputOutput[$property] = new DateTimeProperty('now');
        } elseif($direction === Direction::OUT && isset($inputOutput[$property])) {
            $inputOutput[$property] = new DateTimeProperty($inputOutput[$property]);
        }

        return $inputOutput;
    }
}