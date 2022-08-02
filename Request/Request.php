<?php

namespace Neoan\Request;

use Neoan\Helper\VerifyJson;
use Neoan\NeoanApp;

class Request
{
    private static ?Request $instance = null;

    public string $requestMethod;
    public array $requestHeaders = [];
    public array $urlParts = [];
    public array $parameters = [];
    public string $requestUri;
    public array $files = [];
    public array $input = [];

    public static function getInstance(): ?Request
    {
        if (self::$instance === null) {
            self::$instance = new Request();
            if (isset($_SERVER['REQUEST_METHOD'])) {
                self::$instance->requestMethod = $_SERVER['REQUEST_METHOD'];
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                self::$instance->requestUri = preg_replace('/[a-z\d\/_.\-]*\/public/i', '', $_SERVER['REQUEST_URI']);
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
        self::parseActualFile($app->publicPath);
        $instance = self::getInstance();

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
        foreach ($_SERVER as $item) {
            if (str_starts_with($item, 'HTTP_')) {
                $instance->requestHeaders[] = $item;
            }
        }
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
    }
    private static function parseActualFile($publicPath): void
    {
        $potential = $publicPath . $_SERVER['REQUEST_URI'];
        if(file_exists($potential) && !is_dir($potential)){

            preg_match('/\.([a-z0-9]+)$/',$potential, $hits);
            switch ($hits[1]) {
                case 'js':
                    header('Content-Type: text/javascript');
                    break;
                case 'css':
                    header('Content-Type: text/css');
                    break;
                case 'svg':
                    header('Content-Type: image/svg+xml');
                    break;

            }
            echo file_get_contents($publicPath .$_SERVER['REQUEST_URI']);
            die();
        }
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
}