<?php

namespace Sanvex\Core;

use Illuminate\Support\Facades\DB;

abstract class BaseDbResource
{
    protected string $entityType = '';

    public function __construct(protected readonly BaseDriver $driver) {}

    public function search(array $filters = []): array
    {
        $query = DB::table('sv_entities')
            ->where('driver', $this->driver->id)
            ->where('entity_type', $this->entityType);

        return $query->limit($filters['limit'] ?? 50)->get()->map(fn($r) => (array) $r)->toArray();
    }

    public function findByEntityId(string $id): ?array
    {
        $record = DB::table('sv_entities')
            ->where('driver', $this->driver->id)
            ->where('entity_type', $this->entityType)
            ->where('entity_id', $id)
            ->first();

        return $record ? (array) $record : null;
    }

    public function findAll(int $limit = 50): array
    {
        return DB::table('sv_entities')
            ->where('driver', $this->driver->id)
            ->where('entity_type', $this->entityType)
            ->limit($limit)
            ->get()
            ->map(fn($r) => (array) $r)
            ->toArray();
    }
}
