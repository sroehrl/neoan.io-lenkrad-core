<?php

namespace Neoan\Enums;

enum ResponseOutput
{
    case HTML;
    case JSON;

    public function output(): string
    {
        return match ($this) {
            self::HTML => 'html',
            self::JSON => 'json'
        };
    }
}