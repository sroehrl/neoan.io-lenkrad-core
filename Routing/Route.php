<?php

namespace Neoan\Routing;

use Neoan\Errors\NotFound;
use Neoan\Request\Request;
use Neoan\Response\Response;

class Route
{
    private static ?Route $instance = null;
    private array $paths = [];
    private string $currentPath;
    private string $currentMethod;

    private static function getInstance(string $path = null) :self
    {
        if (self::$instance === null)
        {
            self::$instance = new Route();
        }
        if($path){
            self::$instance->currentPath = $path;
        }

        return self::$instance;
    }

    public function response(array $responseHandler): void
    {
        $instance = self::getInstance();
        $instance->paths[$instance->currentMethod][$instance->currentPath]['response'] = $responseHandler;
    }

    public function inject(array $injections): self
    {
        $instance = self::getInstance();
        $instance->paths[$instance->currentMethod][$instance->currentPath]['injections'] = $injections;
        return $instance;
    }
    public function view(string $path): self
    {
        $instance = self::getInstance();
        $instance->paths[$instance->currentMethod][$instance->currentPath]['view'] = $path;
        return $instance;
    }

    public static function request(string $method, string $path, ...$classNames) : self
    {
        $instance = self::getInstance($path);
        $instance->currentMethod = $method;
        $instance->paths[$method][$path] = ['classes' => [...$classNames], 'injections' => [], 'response' => []];
        return $instance;
    }

    public static function get(string $path, ...$classNames): self
    {
        return self::request('GET', $path, ...$classNames);
    }

    public static function post(string $path, ...$classNames): self
    {
        return self::request('POST', $path, ...$classNames);
    }
    public static function put(string $path, ...$classNames): self
    {
        return self::request('PUT', $path, ...$classNames);
    }
    public static function patch(string $path, ...$classNames): self
    {
        return self::request('PATCH', $path, ...$classNames);
    }
    public static function delete(string $path, ...$classNames): self
    {
        return self::request('DELETE', $path, ...$classNames);
    }

    public function __invoke(): void
    {
        $instance = self::getInstance();
        if(!isset($instance->paths[Request::getRequestMethod()])){
            new NotFound(Request::getRequestUri());
        }
        $found = false;
        foreach ($instance->paths[Request::getRequestMethod()] as $path => $route) {
            $expression = str_replace('/','\/', preg_replace('/:[^\/]+/', '([^/]+)', $path));
            if (preg_match("/$expression$/",Request::getRequestUri(), $matches)) {
                $found = true;
                if(count($matches)>1){
                    array_shift($matches);
                    $instance->handleParameters($path, $matches);
                }
                $instance->execute($route);
            }
        }
        if(!$found) {
            new NotFound(Request::getRequestUri());
        }
    }

    /**
     * @throws \Exception
     */
    private function execute(array $route): void
    {
        if(empty($route['classes'])) {
            Response::output($route['injections'], [$route['view']]);
        } else {
            $passIn = $route['injections'];
            foreach ($route['classes'] as $i => $class) {

                $run = new $class();
                if(!$run instanceof Routable){
                    throw new \Exception($class.' needs to implement ' . Routable::class, 500);
                }
                $result = $run($passIn);
                $passIn = $this->packUnpack($passIn, $result);
                if($i + 1 === sizeof($route['classes'])){
                    if($route['response']){
                        $route['response'][0]::{$route['response'][1]}($result, $route['view'] ?? null);
                    } else {
                        Response::output($result, [$route['view'] ?? null]);
                    }

                }
            }
        }

    }
    private function packUnpack(array $existing, mixed $previousResult): array
    {
        if(is_array($previousResult) || $previousResult instanceof \Traversable){
            return [...$existing, ...$previousResult];
        } else {
            return [...$existing, $previousResult];
        }
    }
    private function handleParameters(string $path, $uriMatches): void
    {
        $instance = self::getInstance();
        preg_match_all('/:([^\/]+)/', $path, $params, PREG_SET_ORDER);
        $expose = [];
        foreach($params as $i => $param) {
            $expose[$param[1]] = $uriMatches[$i];
        }
        Request::setParameters($expose);
    }
}