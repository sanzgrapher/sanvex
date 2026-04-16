<?php

namespace Sanvex\Core\Database;

use Illuminate\Support\Facades\DB;

class QueryBuilder
{
    private string $table;
    private array $wheres = [];
    private ?int $limitValue = null;
    private ?int $offsetValue = null;
    private array $orders = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function for(string $table): self
    {
        return new self($table);
    }

    public function where(string $column, mixed $value): static
    {
        $this->wheres[] = [$column, '=', $value];
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset(int $offset): static
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orders[] = [$column, $direction];
        return $this;
    }

    public function get(): array
    {
        $query = DB::table($this->table);

        foreach ($this->wheres as [$col, $op, $val]) {
            $query->where($col, $op, $val);
        }

        foreach ($this->orders as [$col, $dir]) {
            $query->orderBy($col, $dir);
        }

        if ($this->limitValue !== null) {
            $query->limit($this->limitValue);
        }

        if ($this->offsetValue !== null) {
            $query->offset($this->offsetValue);
        }

        return $query->get()->map(fn($r) => (array) $r)->toArray();
    }

    public function first(): ?array
    {
        $results = $this->limit(1)->get();
        return $results[0] ?? null;
    }
}
