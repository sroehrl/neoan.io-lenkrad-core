<?php

namespace Neoan\Request;

use Neoan\Response\Response;

class RequestGuard
{
    const requestTypes = ['query', 'parameter', 'post', 'file'];

    const throwOnError = true;

    private function getRequestTypes(): array
    {
        $result = [];
        $possible = [
            'query' => [
                'method' => 'getQuery',
                'keys' => array_keys(Request::getQueries())
            ],
            'parameter' => [
                'method' => 'getParameter',
                'keys' => array_keys(Request::getParameters())
            ],
            'post' => [
                'method' => 'getInput',
                'keys' => array_keys(Request::getInputs())
            ],
            'file' => [
                'method' => 'getFile',
                'keys' => array_keys(Request::getFiles())
            ]
        ];
        foreach (self::requestTypes as $requestType) {
            $result[$possible[$requestType]['method']] = $possible[$requestType]['keys'];
        }
        return $result;
    }

    public function __invoke(): static
    {
        $reflection = new \ReflectionClass(static::class);

        foreach ($reflection->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $filled = false;
            foreach ($this->getRequestTypes() as $which => $fillable){
                if(in_array($property->getName(), $fillable)){
                    $filled = true;
                    $interim = Request::$which($property->getName());
                    if($property->getType()->isBuiltin()){
                        settype($interim, $property->getType()->getName());
                    } elseif(enum_exists($property->getType()->getName())) {
                        $enum = $property->getType()->getName();
                        $interim = $enum::tryFrom($interim);
                        if(!$property->getType()->allowsNull() && $interim === null) {
                            $filled = false;
                        }
                    } else {
                        $class = $property->getType()->getName();
                        $interim = new $class($interim);
                    }
                    try{
                        $this->{$property->getName()} = $interim;
                    } catch (\TypeError $e) {
                        $filled = false;
                    }
                }
            }
            if(!$filled && self::throwOnError && !$property->getType()->allowsNull()){
                $response = new Response();
                http_response_code(400);
                // response type?
                $response->respond(json_encode([
                    'msg' => 'Bad Request',
                    'reason' => 'missing input: ' . $property->getName()
                ]));
            }
        }
        return $this;
    }
}