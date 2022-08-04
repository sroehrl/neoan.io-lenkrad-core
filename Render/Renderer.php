<?php

namespace Neoan\Render;


use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Event\Listenable;
use Neoan3\Apps\Template;

class Renderer implements RenderEngine, Listenable
{
    private static ?self $instance = null;
    protected string $templatePath;
    protected ?string $htmlSkeletonPath = null;
    protected string $htmlComponentPlacement = 'main';
    protected ?array $skeletonVariables = [];

    private static function getInstance(): ?Renderer
    {
        if(self::$instance == null){
            self::$instance = new Renderer();
        }
        return self::$instance;
    }
    public static function setTemplatePath(string $path): void
    {
        $instance = self::getInstance();
        $instance->templatePath = $path;
    }
    public static function setHtmlSkeleton(string $fileLocation, string $componentPlacement = 'main', array $skeletonVariables = ['skeletonKey'=>'value']): void
    {
        $instance = self::getInstance();
        $instance->htmlSkeletonPath = $fileLocation;
        $instance->skeletonVariables = $skeletonVariables;
    }
    public static function render(array $data = [], $view = null)
    {
        $instance = self::getInstance();
        Event::dispatch(GenericEvent::BEFORE_RENDERING, ['data'=>$data, 'view'=>$view, 'instance' => $instance]);
        if($instance->htmlSkeletonPath){
            $data = [...$data, ...$instance->skeletonVariables];
            $data[$instance->htmlComponentPlacement] = Template::embraceFromFile($instance->templatePath . $view, $data);
            return Template::embraceFromFile($instance->htmlSkeletonPath, $data);
        }
        return Template::embraceFromFile($instance->templatePath . $view, $data);
    }
}