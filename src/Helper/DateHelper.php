<?php

namespace Neoan\Helper;

use DateTime;

class DateHelper extends DateTime
{

    public function __construct(string|int|null $input = 'now', $timezone = null)
    {
        parent::__construct($this->parseDateInput($input), $timezone);
    }

    private function parseDateInput(mixed $input): string
    {
        if(is_null($input)){
            $input = 'now';
        }
        if (is_numeric($input)) {
            $input = date('Y-m-d H:i:s', $input);
        }
        return $input;
    }

    public function __toString()
    {
        return $this->format('Y-m-d H:i:s');
    }
}