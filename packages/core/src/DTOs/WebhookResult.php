<?php

namespace Sanvex\Core\DTOs;

use Sanvex\Core\Tenancy\Owner;

class WebhookResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?array $response = null,
        public readonly int $status = 200,
        public readonly ?string $driver = null,
        public readonly ?string $eventType = null,
        public readonly ?string $error = null,
        public readonly ?Owner $owner = null,
    ) {}

    public static function ok(array $response = [], ?string $driver = null, ?string $eventType = null): self
    {
        return new self(
            success: true,
            response: $response,
            status: 200,
            driver: $driver,
            eventType: $eventType,
        );
    }

    public static function fail(string $error, int $status = 400, ?string $driver = null): self
    {
        return new self(
            success: false,
            error: $error,
            status: $status,
            driver: $driver,
        );
    }

    public function withOwner(mixed $owner): self
    {
        return new self(
            success: $this->success,
            response: $this->response,
            status: $this->status,
            driver: $this->driver,
            eventType: $this->eventType,
            error: $this->error,
            owner: Owner::resolve($owner),
        );
    }
}
