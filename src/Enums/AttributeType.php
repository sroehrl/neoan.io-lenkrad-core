<?php

namespace Neoan\Enums;

enum AttributeType: string
{
    case ATTACH = 'attach';
    case MUTATE = 'mutate';
    case DECLARE = 'declare';
    case INITIAL = 'initial';
    case PRIVATE = 'private';

}