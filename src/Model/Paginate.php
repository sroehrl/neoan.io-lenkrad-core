<?php

namespace Neoan\Model;



use Neoan\Database\Database;
use Neoan\Helper\DataNormalization;

class Paginate
{
    private array $condition = [];
    private array $orderBy;
    public function __construct(private readonly int $page, private readonly int $pageSize, private readonly string $model)
    {
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    public function where(array $condition): self
    {
        $this->condition = $condition;
        return $this;
    }

    public function ascending(string $column): static
    {
        $this->orderBy = [$column, 'ASC'];
        return $this;
    }
    public function descending(string $column): static
    {
        $this->orderBy = [$column, 'DESC'];
        return $this;
    }

    public function get(): array
    {
        $modelDescription = $this->model::declare();
        $table = array_key_first($modelDescription);
        $select = $table . '.' . $modelDescription[$table][0]['name'];
        foreach ($condition as $key => $value) {
            if(str_contains($key, '.')) {
                $select .= ' ' . $key;
            }
        }
        $total = count(Database::easy($select, $this->condition));
        $start = ($this->page - 1) * $this->pageSize;
        $callFunctions = [];
        if(isset($this->orderBy)){
            $callFunctions['orderBy'] = $this->orderBy;
        }
        $callFunctions['limit'] = [$start, $this->pageSize];

        $data = $this->model::retrieve($this->condition, $callFunctions);
        return [
            'page' => $this->page,
            'pageSize' => $this->pageSize,
            'total' => $total,
            'pages' => ceil($total/$this->pageSize),
            'collection' => $data
        ];
    }

}