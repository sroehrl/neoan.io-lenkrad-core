<?php

namespace Neoan\Request;

use Neoan\CoreInterfaces\RequestInterface;
use Neoan\Enums\GenericEvent;
use Neoan\Event\Event;
use Neoan\Helper\FileParser;
use Neoan\Helper\Terminate;
use Neoan\Helper\VerifyJson;
use Neoan\NeoanApp;

class Request implements RequestInterface
{
    private static ?RequestInterface $instance = null;

    public string $requestMethod;
    public array $requestHeaders = [];
    public array $urlParts = [];
    public array $parameters = [];
    public array $queryParts = [];
    public string $requestUri;
    public string $webPath;
    public array $files = [];
    public array $input = [];

    public static function setParameters(array $parameters): void
    {
        $instance = self::getInstance();
        $instance->parameters = $parameters;
    }
    public static function setParameter(string $key, ?string $value)
    {
        $instance = self::getInstance();
        $instance->parameters[$key] = $value;
    }

    public static function getQueries(): array
    {
        $instance = self::getInstance();
        return $instance->queryParts;
    }

    public static function getQuery(string $name): ?string
    {
        $instance = self::getInstance();
        return $instance->queryParts[$name] ?? null;
    }

    public static function setQueries(array $queryParameter): void
    {
        $instance = self::getInstance();
        $instance->queryParts = $queryParameter;
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

    public static function redirect(string $whereTo)
    {
        header('location: ' . $whereTo);
        Terminate::exit();
    }

    public function __invoke(NeoanApp $app = null, $additional = null): void
    {
        new FileParser($app->publicPath . $_SERVER['REQUEST_URI']);
        $instance = self::getInstance(null, $app->webPath);
        self::parseInput();
        self::parseRequestHeaders();

    }

    public static function getInstance($mockMe = null, $webPath = ''): ?RequestInterface
    {
        if ($mockMe) {
            self::$instance = $mockMe;
            self::$instance->webPath = $webPath;
        }
        if (self::$instance === null) {
            self::$instance = new Request();
            self::$instance->webPath = $webPath;
            if (isset($_SERVER['REQUEST_METHOD'])) {
                self::$instance->requestMethod = $_SERVER['REQUEST_METHOD'];
            }
            if (isset($_SERVER['REQUEST_URI'])) {
                self::$instance->processQueryParametersFromRequestUri();
                self::$instance->processRequestUriSanitation();
                self::$instance->createUrlParts();
            }
            if (!empty($_FILES)) {
                self::$instance->files = $_FILES;
            }
        }
        return self::$instance;
    }

    private function processQueryParametersFromRequestUri(): void
    {
        // extract query-params
        $query = $_SERVER['QUERY_STRING'] ?? '';
        parse_str($query, self::$instance->queryParts);
        self::$instance->requestUri = $_SERVER['REQUEST_URI'];
        if ($query !== '') {
            self::$instance->requestUri = mb_substr($_SERVER['REQUEST_URI'], 0, -1 * (mb_strlen($query)+1));
        }

    }

    private function processRequestUriSanitation(): void
    {
        $sanitizedWebPath = str_replace('/', '\\/', self::$instance->webPath);
        $pattern = "[a-z\d\/_.\-]*\/{$sanitizedWebPath}";
        self::$instance->requestUri = preg_replace("/$pattern/i", '', self::$instance->requestUri);
    }

    private function createUrlParts(): void
    {
        self::$instance->urlParts = array_values(array_filter(explode('/', self::$instance->requestUri)));
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
}