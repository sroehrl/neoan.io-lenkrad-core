<?php

namespace Neoan\Helper;

use Iterator;
use Neoan\Model\Collection;
use Neoan\Model\Model;
use Neoan\Store\Dynamic;

class DataNormalization implements Iterator
{
    private static ?self $instance = null;
    public array $converted = [];

    public function __construct(mixed $data = null)
    {
        if ($data) {
            $this->converted = $this->convert($data);
        }
    }

    private function convert(mixed $data): mixed
    {
        if ($data instanceof Model || $data instanceof Collection) {
            $data = $data->toArray();
        } elseif ($data instanceof Dynamic) {
            $data = $data->get();
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->convert($value);
            }
        }
        return $data;
    }

    static function normalize(mixed $data): self
    {
        $instance = self::getInstance();
        $instance->converted = $instance->convert($data);
        return $instance;
    }

    static function getInstance($mockMe = null)
    {
        if ($mockMe) {
            self::$instance = $mockMe;
        }
        if (!self::$instance) {
            self::$instance = new DataNormalization();
        }
        return self::$instance;
    }

    public function current(): mixed
    {
        return current($this->converted);
    }

    public function next(): void
    {
        next($this->converted);
    }

    public function valid(): bool
    {
        return array_key_exists($this->key(), $this->converted);
    }

    public function key(): mixed
    {
        return key($this->converted);
    }

    public function rewind(): void
    {
        reset($this->converted);
    }
}