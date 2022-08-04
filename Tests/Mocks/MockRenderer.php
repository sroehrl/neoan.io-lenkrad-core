<?php

namespace Test\Mocks;

use Neoan\Render\RenderEngine;

class MockRenderer implements RenderEngine
{
    protected string $templatePath;
    protected ?string $htmlSkeletonPath = null;
    protected string $htmlComponentPlacement = 'main';
    protected ?array $skeletonVariables = [];
    /**
     * @throws \Exception
     */
    public static function render(array $data = [], string $view = null): array
    {
        throw new \Exception('renderer');
    }

    public static function detachInstance()
    {
        // TODO: Implement detachInstance() method.
    }
}