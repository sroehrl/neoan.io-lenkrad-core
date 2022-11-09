<?php

namespace Neoan\Enums;

enum TimePeriod
{
    case YEARS;
    case MONTHS;
    case DAYS;
    case WEEKS;
    case HOURS;
    case MINUTES;
    case SECONDS;

    function getPeriod(int $amount): string
    {
        $timeSignifier = match ($this){
            TimePeriod::HOURS, TimePeriod::MINUTES, TimePeriod::SECONDS => 'T',
            default => ''
        };
        $designator = match ($this){
            TimePeriod::YEARS => 'Y',
            TimePeriod::MONTHS, TimePeriod::MINUTES => 'M',
            TimePeriod::DAYS => 'D',
            TimePeriod::WEEKS => 'W',
            TimePeriod::HOURS => 'H',
            TimePeriod::SECONDS => 'S'
        };

        return 'P' . $timeSignifier . $amount . $designator;
    }
}
