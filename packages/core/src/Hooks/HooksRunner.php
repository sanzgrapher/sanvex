<?php

namespace Sanvex\Core\Hooks;

class HooksRunner
{
    private array $hooks = [];

    public function register(string $event, callable $callback): void
    {
        $this->hooks[$event][] = $callback;
    }

    public function run(string $event, mixed $payload): mixed
    {
        foreach ($this->hooks[$event] ?? [] as $hook) {
            $payload = $hook($payload);
        }

        return $payload;
    }
}
