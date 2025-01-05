<?php

namespace Neoan\Database;

enum Operandi: string
{
    case GREATER_THEN = ' > ?';
    case LESSER_THEN = ' < ?';
    case LIKE = ' LIKE ?';
    case UNHEX = ' UNHEX(?)';
    case FORCE_EQUAL = ' = ?';


    /**
     * @throws \Exception
     */
    public static function matchValue(string $value): self
    {
        if(empty($value)){
            throw new \Exception('Unset/uninitialized value');
        }
        return match ($value[0]) {
            '>' => self::GREATER_THEN,
            '<' => self::LESSER_THEN,
            '%' => self::LIKE,
            '$' => self::UNHEX,
            '=' => self::FORCE_EQUAL,
        };
    }

    public function setNamedParameter(string $name): string
    {
        return str_replace('?', ':' . $name, $this->value);
    }

}
