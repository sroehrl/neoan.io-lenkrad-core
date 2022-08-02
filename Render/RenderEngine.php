<?php

namespace Neoan\Render;

interface RenderEngine
{
    public static function render(array $data = [], string $view = null);
}