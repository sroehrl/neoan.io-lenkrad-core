<?php

namespace Neoan\Model\Transformers;

use Neoan\Enums\Direction;
use Neoan\Helper\DateHelper;
use Neoan\Model\Interfaces\Transformation;

class CurrentTimeIn implements Transformation
{

    public function __invoke(array $inputOutput, Direction $direction, string $property): array
    {
        if ($direction === Direction::IN) {
            $inputOutput[$property] = new DateHelper();
        }
        return $inputOutput;
    }
}