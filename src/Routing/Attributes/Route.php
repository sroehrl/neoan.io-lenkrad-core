<?php

namespace Neoan\Routing\Attributes;

use Attribute;
use Neoan\Enums\RequestMethod;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_ALL)]
class Route implements RouteAttribute
{
    private string $controllerClass;
    private string $route;
    /**
     * @var array[]
     */
    private array $middleware;
    private RequestMethod $method;

    public function __construct(RequestMethod $method, string $route, ...$middleware)
    {
        $this->route = $route;
        $this->middleware = [...$middleware];
        $this->method = $method;
    }
    public function setControllerClass(string $qualifiedName)
    {
        $this->controllerClass = $qualifiedName;
    }
    public function generateRoute(): void
    {
        $chain = [...$this->middleware];
        $chain[] = $this->controllerClass;
        \Neoan\Routing\Route::request($this->method, $this->route, ...$chain);
    }
}