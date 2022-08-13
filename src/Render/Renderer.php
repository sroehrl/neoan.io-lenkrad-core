<?php

namespace Neoan\Render;


use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Event\Listenable;
use Neoan\Helper\DataNormalization;
use Neoan\Response\Response;
use Neoan\Store\Dynamic;
use Neoan3\Apps\Template;

class Renderer implements RenderEngine, Listenable
{
    private static ?RenderEngine $instance = null;
    protected string $templatePath = '/';
    protected ?string $htmlSkeletonPath = null;
    protected string $htmlComponentPlacement = 'main';
    protected ?array $skeletonVariables = [];

    public static function getInstance($mockMe = null): ?RenderEngine
    {
        if ($mockMe) {
            self::$instance = $mockMe;
        }
        if (self::$instance == null) {
            self::$instance = new Renderer();
        }
        return self::$instance;
    }

    public static function setTemplatePath(string $path): void
    {
        $instance = self::getInstance();
        $instance->templatePath = $path;
    }

    /**
     * @return string
     */
    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    /**
     * @return string|null
     */
    public function getHtmlSkeletonPath(): ?string
    {
        return $this->htmlSkeletonPath;
    }

    /**
     * @return string
     */
    public function getHtmlComponentPlacement(): string
    {
        return $this->htmlComponentPlacement;
    }

    /**
     * @return array|null
     */
    public function getSkeletonVariables(): ?array
    {
        return $this->skeletonVariables;
    }


    public static function setHtmlSkeleton(string $fileLocation, string $componentPlacement = 'main', array $skeletonVariables = ['skeletonKey' => 'value']): void
    {
        $instance = self::getInstance();
        $instance->htmlSkeletonPath = $fileLocation;
        $instance->htmlComponentPlacement = $componentPlacement;
        $instance->skeletonVariables = $skeletonVariables;
    }

    public static function render(array|DataNormalization $data = [], $view = null)
    {
        $instance = self::getInstance();
        Event::dispatch(GenericEvent::BEFORE_RENDERING, ['data' => $data, 'view' => $view, 'instance' => $instance]);
        $viewLocation = $instance->templatePath . $view;
        if ($instance->htmlSkeletonPath) {
            $data = self::compressData($data, $view);
            $viewLocation = $instance->htmlSkeletonPath;
        }
        return Template::embraceFromFile($viewLocation, $data);
    }

    private static function compressData($data, $view): array
    {
        $instance = self::getInstance();

        $data = [
            ...$data,
            ...new DataNormalization($instance->skeletonVariables)
        ];
        $data[$instance->htmlComponentPlacement] = Template::embraceFromFile($instance->templatePath . $view, $data);
        return $data;
    }

    public static function detachInstance()
    {
        self::$instance = null;
    }
}