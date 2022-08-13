<?php

namespace Neoan\Response;

use Neoan\CoreInterfaces\ResponseInterface;
use Neoan\Enums\GenericEvent;
use Neoan\Enums\ResponseOutput;
use Neoan\Event\Event;
use Neoan\Helper\Terminate;
use Neoan\Helper\VerifyJson;
use Neoan\Model\Collection;
use Neoan\Model\Model;
use Neoan\Render\RenderEngine;
use Neoan\Render\Renderer;
use Neoan\Store\Dynamic;

class Response implements ResponseInterface
{
    private static ?ResponseInterface $instance = null;
    public array $responseHeaders = [];
    protected string $defaultOutput = 'json';
    protected string $defaultRenderer = Renderer::class;

    public function __invoke(): ResponseInterface
    {
        return self::getInstance();
    }

    public static function getInstance($mockMe = null): ResponseInterface
    {
        if($mockMe){
            self::$instance = $mockMe;
        }
        if (self::$instance === null) {
            self::$instance = new Response();
        }
        return self::$instance;
    }

    public function setResponseHeaders(...$responseHeaders): ResponseInterface
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
        Terminate::die();
    }


    static public function setDefaultOutput(ResponseOutput $output): void
    {
        self::getInstance()->defaultOutput = $output->output();
    }
    static public function getDefaultOutput(): string
    {
        return self::getInstance()->defaultOutput;
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
        $json = new VerifyJson($data);
        self::getInstance()
            ->setResponseHeaders('Content-type: application/json')
            ->respond($json->jsonSerialize());
    }

    static public function html(mixed $data, ?string $view = null): void
    {
        $instance = self::getInstance();
        $data = self::normalizeData($data);

        $instance->setResponseHeaders('Content-type: text/html')
            ->respond($instance->defaultRenderer::render($data, $view));
    }
    public static function normalizeData(mixed $data): array
    {
        if($data instanceof Model || $data instanceof Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof Dynamic){
            $data = $data->get();
        } elseif(is_array($data)) {
            foreach ($data as $key => $value){
                $data[$key] = self::normalizeData($value);
            }
        }
        return $data;
    }

    public static function detachInstance(): void
    {
        self::$instance = null;
    }
}