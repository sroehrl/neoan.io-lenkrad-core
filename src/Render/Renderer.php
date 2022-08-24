<?php

namespace Neoan\Render;


use Neoan\Enums\GenericEvent;
use Neoan\Errors\NotFound;
use Neoan\Event\Event;
use Neoan\Event\Listenable;
use Neoan\Helper\DataNormalization;
use Neoan3\Apps\Template\Template;

class Renderer implements RenderEngine, Listenable
{
    private static ?RenderEngine $instance = null;
    protected string $templatePath = '/';
    protected ?string $htmlSkeletonPath = null;
    protected string $htmlComponentPlacement = 'main';
    protected ?DataNormalization $skeletonVariables;

    public static function setTemplatePath(string $path): void
    {
        $instance = self::getInstance();
        $instance->templatePath = $path;
    }

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

    public static function setHtmlSkeleton(string $fileLocation, string $componentPlacement = 'main', array $skeletonVariables = ['skeletonKey' => 'value']): void
    {
        $instance = self::getInstance();
        $instance->htmlSkeletonPath = $fileLocation;
        $instance->htmlComponentPlacement = $componentPlacement;
        $instance->skeletonVariables = new DataNormalization($skeletonVariables);
    }

    public static function render(DataNormalization|array $data = [], $view = null): string
    {
        $instance = self::getInstance();
        if(!$data instanceof DataNormalization) {
            $data = new DataNormalization($data);
        }
        Event::dispatch(GenericEvent::BEFORE_RENDERING, ['data' => $data, 'view' => $view, 'instance' => $instance]);
        $viewLocation = $instance->templatePath . $view;
        if ($instance->htmlSkeletonPath) {
            $data = self::compressData($data, $view);
            $viewLocation = $instance->htmlSkeletonPath;
        }
        return Template::embraceFromFile($viewLocation, $data->toArray());
    }

    private static function compressData(DataNormalization $data, $view): DataNormalization
    {
        $instance = self::getInstance();
        $data->add($instance->skeletonVariables);
        $add = [];
        $add[$instance->htmlComponentPlacement] = Template::embraceFromFile($instance->templatePath . $view, $data->toArray());
        $data->add($add);
        return $data;
    }

    public static function detachInstance()
    {
        self::$instance = null;
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
        return $this->skeletonVariables->toArray();
    }
}