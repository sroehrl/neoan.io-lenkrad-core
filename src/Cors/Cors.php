<?php

namespace Neoan\Cors;

use Neoan\Helper\Terminate;

class Cors
{
    private array $options = [
        'Access-Control-Allow-Origin' => [],
        'Access-Control-Allow-Methods' => ['POST', 'PUT', 'GET', 'DELETE', 'OPTIONS'],
        'Access-Control-Allow-Headers' => ['Content-Type', 'X-Auth-Token', 'Authorization', 'Origin']
    ];
    private bool $allowOptionsCall = false;

    public function addAllowedOrigin(string $origin): static
    {
        $this->options['Access-Control-Allow-Origin'][] = $origin;
        return $this;
    }

    public function getAllowedOrigins(): array
    {
        return $this->options['Access-Control-Allow-Origin'];
    }

    public function setAllowedMethods(array $allowedMethods): static
    {
        $this->options['Access-Control-Allow-Methods'] = $allowedMethods;
        return $this;
    }

    public function addAllowedMethod(string $method): static
    {
        $this->options['Access-Control-Allow-Methods'][] = strtoupper($method);
        return $this;
    }

    public function getAllowedMethods() : array
    {
        return $this->options['Access-Control-Allow-Methods'];
    }

    public function setAllowedHeaders(array $allowedHeaders): static
    {
        $this->options['Access-Control-Allow-Headers'] = $allowedHeaders;
        return $this;
    }

    public function addAllowedHeader(string $method): static
    {
        $this->options['Access-Control-Allow-Headers'][] = strtoupper($method);
        return $this;
    }

    public function getAllowedHeaders() : array
    {
        return $this->options['Access-Control-Allow-Headers'];
    }

    public function setAllowMethodOptions(bool $allowOptions): static
    {
        $this->allowOptionsCall = $allowOptions;
        return $this;
    }

    public function __invoke(): static
    {
        foreach ($this->options as $header => $values) {
            if(!empty($values)){
                header($header . ': ' . implode(', ', $values));
            }
        }
        if(!$this->allowOptionsCall && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS'){
            Terminate::exit();

        }
        return $this;
    }

}