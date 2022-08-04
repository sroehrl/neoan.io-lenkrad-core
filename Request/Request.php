<?php

namespace Neoan\Request;

use Neoan\CoreInterfaces\RequestInterface;
use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Helper\FileParser;
use Neoan\Helper\VerifyJson;
use Neoan\NeoanApp;

class Request implements RequestInterface
{
    private static ?RequestInterface $instance = null;

    public string $requestMethod;
    public array $requestHeaders = [];
    public array $urlParts = [];
    public array $parameters = [];
    public string $requestUri;
    public string $webPath;
    public array $files = [];
    public array $input = [];

    public static function getInstance($mockMe = null, $webPath = ''): ?RequestInterface
    {
        if($mockMe){
            self::$instance = $mockMe;
        }
        if (self::$instance === null) {
            self::$instance = new Request();
            self::$instance->webPath = $webPath;
            if (isset($_SERVER['REQUEST_METHOD'])) {
                self::$instance->requestMethod = $_SERVER['REQUEST_METHOD'];
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                $sanitizedWebPath = str_replace('/','\\/',$webPath);
                $pattern = "[a-z\d\/_.\-]*\/{$sanitizedWebPath}";
                self::$instance->requestUri = preg_replace("/$pattern/i", '', $_SERVER['REQUEST_URI']);
                self::$instance->urlParts = array_values(array_filter(explode('/', self::$instance->requestUri)));
            }
            if (!empty($_FILES)) {
                self::$instance->files = $_FILES;
            }
        }
        return self::$instance;
    }

    public function __invoke(NeoanApp $app): void
    {
        new FileParser($app->publicPath. $_SERVER['REQUEST_URI']);
        $instance = self::getInstance(null, $app->webPath);
        self::parseInput();
        self::parseRequestHeaders();

    }


    public static function setParameters(array $parameters): void
    {
        $instance = self::getInstance();
        $instance->parameters = $parameters;
    }

    private static function parseRequestHeaders(): void
    {
        $instance = self::getInstance();
        foreach ($_SERVER as $key => $item) {
            if (str_starts_with($key, 'HTTP_')) {
                $instance->requestHeaders[$key] = $item;
            }
        }
        Event::dispatch(GenericEvent::REQUEST_HEADERS_SET, $instance->requestHeaders);
    }

    private static function parseInput(): void
    {
        $instance = self::getInstance();
        $data = file_get_contents('php://input');
        if (VerifyJson::isJson($data)) {
            $instance->input = json_decode($data, true);
        } elseif (!empty($_POST)) {
            $instance->input = $_POST;
        }
        Event::dispatch(GenericEvent::REQUEST_INPUT_PARSED, $instance->input);
    }

    public static function getRequestMethod(): string
    {
        $instance = self::getInstance();
        return $instance->requestMethod;
    }

    public static function getRequestUri(): string
    {
        $instance = self::getInstance();
        return $instance->requestUri;
    }

    public static function getInputs(): array
    {
        $instance = self::getInstance();
        return $instance->input;
    }
    public static function getInput(string $which): ?string
    {
        $instance = self::getInstance();
        return $instance->input[$which] ?? null;
    }
    public static function getParameters(): array
    {
        $instance = self::getInstance();
        return $instance->parameters;
    }
    public static function getParameter(string $which): ?string
    {
        $instance = self::getInstance();
        return $instance->parameters[$which] ?? null;
    }

    public static function detachInstance(): void
    {
        self::$instance = null;
    }
}