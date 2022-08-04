<?php

namespace Neoan\Response;

use Neoan\Enums\GenericEvent;
use Neoan\Enums\ResponseOutput;
use Neoan\Event\Event;
use Neoan\Helper\VerifyJson;
use Neoan\Render\RenderEngine;
use Neoan\Render\Renderer;

class Response
{
    private static ?Response $instance = null;
    public array $responseHeaders = [];
    protected string $defaultOutput = 'json';
    protected string $defaultRenderer = Renderer::class;

    public function __invoke(): self
    {
        return self::getInstance();
    }

    private static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new Response();
        }
        return self::$instance;
    }

    public function setResponseHeaders(...$responseHeaders): self
    {
        $instance = self::getInstance();
        foreach ($responseHeaders as $responseHeader) {
            $instance->responseHeaders[] = $responseHeader;
        }
        return $instance;
    }

    public function respond(string $dataStream): void
    {
        Event::dispatch(GenericEvent::BEFORE_RESPONSE, [
            'handler' => self::getInstance()->defaultOutput,
            'dataStream' => $dataStream,
        ]);
        foreach ($this->responseHeaders as $header) {
            header($header);
        }
        echo $dataStream;
        die();
    }


    static public function setDefaultOutput(ResponseOutput $output): void
    {
        self::getInstance()->defaultOutput = $output->output();
    }

    /**
     * @throws \Exception
     */
    static public function setDefaultRenderer(string $renderer): void
    {
        $implements = array_keys(class_implements($renderer));
        if(!in_array(RenderEngine::class, $implements)){
            throw new \Exception('Renderer not compatible!', 500);
        }
        self::getInstance()->defaultRenderer = $renderer;
    }

    static public function output($data, array $renderOptions): void
    {
        self::{self::getInstance()->defaultOutput}($data, ...$renderOptions);
    }

    static public function json($data): void
    {
        self::getInstance()
            ->setResponseHeaders('Content-type: application/json')
            ->respond(json_encode(new VerifyJson($data),JSON_THROW_ON_ERROR));
    }

    static public function html($data, ?string $view = null): void
    {
        $instance = self::getInstance();
        $renderer = preg_replace('/::class/','', $instance->defaultRenderer);
        $instance->setResponseHeaders('Content-type: text/html')
            ->respond($renderer::render($data, $view));
    }
}