<?php

namespace Neoan\Database;

enum Selectandi: string
{
    case NOTNULL = 'IS NOT NULL';
    case NULL = 'IS NULL';


    public static function matchHit(string $hit): self
    {
        return match ($hit) {
            '^' => self::NOTNULL,
            '!' => self::NULL,
        };
    }
}

