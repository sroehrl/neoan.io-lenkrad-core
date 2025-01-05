<?php

namespace Test\Mocks;

class MockPDO
{
    private array $data;
    private int $runner = 0;
    private string $sql;

    public function setData(array $data): void
    {
        $this->data = $data;
    }
    public function prepare($sql): MockStm
    {
        $use = $this->data[$this->runner];
        $this->runner++;
        return new MockStm($use);
    }

    public function query($sql): MockStm
    {
        $use = $this->data[$this->runner];
        $this->runner++;
        return new MockStm($use);
    }
    public function lastInsertId(): int
    {
        $use = $this->data[$this->runner];
        $this->runner++;
        return $use['id'];
    }
}

readonly class MockStm
{
    public function __construct(private array $data)
    {
    }

    public function fetchAll(): array
    {
        return $this->data;
    }
    public function execute(?array $params = null): MockStm
    {
        return new MockStm($this->data);
    }

    public function rowCount(): int
    {
        return count($this->data);
    }
    public function errorCode(): bool
    {
        return false;
    }
}