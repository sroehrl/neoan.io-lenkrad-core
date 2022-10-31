<?php

namespace Neoan\Routing;

use Exception;
use Neoan\Enums\GenericEvent;
use Neoan\Enums\RequestMethod;
use Neoan\Errors\NotFound;
use Neoan\Event\Event;
use Neoan\NeoanApp;
use Neoan\Provider\DefaultProvider;
use Neoan\Provider\Injections;
use Neoan\Provider\Interfaces\Provide;
use Neoan\Request\Request;
use Neoan\Response\Response;
use Neoan\Routing\Interfaces\Routable;
use Traversable;

class Route
{
    private static ?Route $instance = null;
    public array $paths = [];
    private string $currentPath;
    private string $currentMethod;
    private Provide $provider;

    public static function get(string $path, ...$classNames): self
    {
        return self::request(RequestMethod::GET, $path, ...$classNames);
    }

    public static function post(string $path, ...$classNames): self
    {
        return self::request(RequestMethod::POST, $path, ...$classNames);
    }

    public static function put(string $path, ...$classNames): self
    {
        return self::request(RequestMethod::PUT, $path, ...$classNames);
    }

    public static function patch(string $path, ...$classNames): self
    {
        return self::request(RequestMethod::PATCH, $path, ...$classNames);
    }

    public static function delete(string $path, ...$classNames): self
    {
        return self::request(RequestMethod::DELETE, $path, ...$classNames);
    }

    public static function request(RequestMethod $method, string $path, ...$classNames): self
    {
        $instance = self::getInstance(null, $path);
        Event::dispatch(GenericEvent::ROUTE_REGISTERED, [
            'method' => $method,
            'route' => $path
        ]);
        $instance->currentMethod = $method->name;
        $instance->paths[$method->name][$path] = ['classes' => [...$classNames], 'injections' => [], 'response' => []];
        return $instance;
    }



    public function response(array $responseHandler): void
    {
        $instance = self::getInstance();
        $instance->paths[$instance->currentMethod][$instance->currentPath]['response'] = $responseHandler;
        Event::dispatch(GenericEvent::RESPONSE_HANDLER_SET, [
            'clientMethod' => __FUNCTION__,
            'handler' => func_get_args()
        ]);
    }

    public static function getInstance($mockMe = null, string $path = null): self
    {
        if (self::$instance === null) {
            self::$instance = new Route();
        }
        if ($path) {
            self::$instance->currentPath = $path;
        }
        Event::dispatch(GenericEvent::ROUTE_HANDLER_INITIALIZED, [
            'clientMethod' => __FUNCTION__,
            'arguments' => func_get_args()
        ]);
        return self::$instance;
    }

    public function inject(array $injections): self
    {
        $instance = self::getInstance();
        Event::dispatch(GenericEvent::ROUTE_INJECTION, [
            'method' => $instance->currentMethod,
            'path' => $instance->currentPath,
            'injections' => $injections
        ]);
        $instance->paths[$instance->currentMethod][$instance->currentPath]['injections'] = [
            ...$instance->paths[$instance->currentMethod][$instance->currentPath]['injections'],
            ...$injections
        ];
        return $instance;
    }

    public function view(string $path): self
    {
        $instance = self::getInstance();
        $instance->paths[$instance->currentMethod][$instance->currentPath]['view'] = $path;
        return $instance;
    }

    public function __invoke(NeoanApp $app): void
    {

        $instance = self::getInstance();
        $instance->provider = $app->injectionProvider;
        if (!isset($instance->paths[Request::getRequestMethod()])) {
            new NotFound(Request::getRequestUri());
        }
        $found = false;
        foreach ($instance->paths[Request::getRequestMethod()] as $path => $route) {
            if($found = $this->evaluateRouteMatch($app->webPath, $path)){

                Event::dispatch(GenericEvent::BEFORE_ROUTABLE_EXECUTION, $route);
                $instance->execute($route);
                break;
            }

        }
        if (!$found) {
            new NotFound(Request::getRequestUri());
        }
    }

    private function evaluateRouteMatch($webPath, $path): bool
    {
        // clean webpath of potential double-/
        $fullPath = $webPath . $path;
        $fullPath = str_replace('//','/', $fullPath);
        $parameters = $this->extractParameters($fullPath);


        $expression = str_replace('/','\/', $fullPath);

        return $this->executePotentialRouteMatch($expression, $parameters);
    }

    private function executePotentialRouteMatch(string $expression, array $parameters): bool
    {
        $hit = preg_match("/^$expression$/", Request::getRequestUri(), $matches);
        if($hit){
            array_shift($matches);
            foreach ($matches as $i => $value){
                Request::setParameter($parameters[$i], str_replace('/','',$value));
            }
            return true;
        }
        return $hit;
    }

    private function extractParameters(string &$fullPath): array
    {
        $hit = preg_match_all('/\/:(\w+)(\*){0,1}/',$fullPath, $matches, PREG_SET_ORDER);
        $parameters = [];
        if($hit){
            foreach ($matches as $matchGroup){
                $parameters[] = $matchGroup[1];
                if(isset($matchGroup[2])) {
                    $fullPath = str_replace($matchGroup[0],'(/[^/]+){0,1}', $fullPath);
                } else {
                    $fullPath = str_replace($matchGroup[0],'/([^/]+)', $fullPath);
                }
            }

        }
        return $parameters;
    }

    /**
     * @throws Exception
     */
    private function execute(array $route): void
    {
        if (empty($route['classes'])) {
            Response::output($route['injections'], [$route['view'] ?? null]);
        } else {
            $this->provider->get(Injections::class, [$route['injections']]);
            foreach ($route['classes'] as $i => $class) {
                $loaded = $this->provider->get($class);

                if ($this->isLastRoutable($route, $i)) {
                    $this->lastRoutable($route, $loaded);
                }
            }
        }

    }

    private function isLastRoutable(array $route, int $index): bool
    {
        return $index + 1 === sizeof($route['classes']);
    }

    private function lastRoutable(array $route, $result): void
    {
        if (!empty($route['response'])) {
            $route['response'][0]::{$route['response'][1]}($result, $route['view'] ?? null);
        } else {
            Response::output($result, [$route['view'] ?? null]);
        }
    }
}