<?php

namespace Neoan\CoreInterfaces;

use Neoan\NeoanApp;

interface RequestInterface
{
    public static function detachInstance(): void;
    public function __invoke(NeoanApp $app = null, $additional = null);
}