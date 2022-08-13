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
    private int $position;

    public function __construct(mixed $data = null)
    {
        $this->position = 0;
        if($data){
            $this->converted = $this->convert($data);
        }
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

    static function normalize(mixed $data): self
    {
        $instance = self::getInstance();
        $instance->converted = $instance->convert($data);
        return $instance;
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

    public function current(): mixed
    {
        return $this->converted[$this->position];
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->converted[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }
}