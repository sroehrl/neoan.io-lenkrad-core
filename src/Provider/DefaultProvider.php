<?php

namespace Neoan\Provider;

use Neoan\Errors\SystemError;
use Neoan\Provider\Interfaces\Provide;
use ReflectionClass;
use ReflectionException;

class DefaultProvider implements Provide
{

    private array $providers = [];

    public function __construct()
    {
        $this->set(static::class, $this);
    }

    public function __invoke(): DefaultProvider
    {
        return $this;
    }

    public function toArray(): array
    {
        return $this->providers;
    }


    public function get(string $id, ?array $parameters = null): mixed
    {
        if (!isset($this->providers[$id])) {
            try{
                $this->set($id, new $id());
            } catch (\Error $e) {
                new SystemError("$id not instantiable");
            }

        }
        return $this->resolve($this->providers[$id], $parameters);
    }

    public function set(string $id, mixed $provider): void
    {
        $this->providers[$id] = $provider;
    }



    public function has(string $id): bool
    {
        return isset($this->providers[$id]);
    }


    public function getDependencies($parameters): array
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            foreach($this->getParameterTypes($parameter) as $dependency) {
                if ($dependency === NULL || $dependency->isBuiltin()) {
                    // check if default value for a parameter is available
                    if ($parameter->isDefaultValueAvailable()) {
                        // get default value of parameter
                        $dependencies[] = $parameter->getDefaultValue();
                    } else {
                        new SystemError("Can not resolve class dependency {$parameter->name}");
                    }
                } else {
                    // get dependency resolved
                    $dependencies[] = $this->get($dependency->getName());
                }
            }

        }

        return $dependencies;
    }

    private function getParameterTypes(\ReflectionParameter $parameter): array
    {
        $reflectionType = $parameter->getType();
        if (!$reflectionType) return [null];

        return $reflectionType instanceof \ReflectionUnionType
            ? $reflectionType->getTypes()
            : [$reflectionType];
    }

    private function resolve($concrete, $parameters = null)
    {

        $reflector = new ReflectionClass($concrete);


        // get invoke method
        try{
            $callable = $reflector->getMethod('__invoke');
        } catch (ReflectionException $e) {
            new SystemError("Class {$reflector->getName()} requires an __invoke method in order to be injected");
        }

        // auto-wire?
        if(!$parameters){
            $parameters   = $callable->getParameters();
            $dependencies = $this->getDependencies($parameters);
        } else {
            $dependencies = $parameters;
        }

        return $concrete(...$dependencies);
    }
}