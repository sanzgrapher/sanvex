<?php

namespace Sanvex\Core\Tenancy;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Sanvex\Core\Contracts\SanvexOwner;

final class Owner
{
    public const GLOBAL_TYPE = 'global';
    public const GLOBAL_ID = 'default';

    public function __construct(
        private readonly string $type,
        private readonly string $id,
    ) {
        if ($this->type === '' || $this->id === '') {
            throw new InvalidArgumentException('Owner type and id must be non-empty strings.');
        }
    }

    public static function global(): self
    {
        static $global = null;

        if (! $global instanceof self) {
            $global = new self(self::GLOBAL_TYPE, self::GLOBAL_ID);
        }

        return $global;
    }

    public static function fromTypeAndId(?string $type, ?string $id): self
    {
        if ($type === null && $id === null) {
            return self::global();
        }

        if ($type === null || $id === null) {
            throw new InvalidArgumentException('Both owner type and owner id are required when one is provided.');
        }

        return new self((string) $type, (string) $id);
    }

    public static function resolve(mixed $owner): self
    {
        if ($owner instanceof self) {
            return $owner;
        }

        if ($owner === null) {
            return self::global();
        }

        if ($owner instanceof SanvexOwner) {
            $key = $owner->sanvexOwnerKey();

            if (! isset($key['type'], $key['id'])) {
                throw new InvalidArgumentException('Sanvex owner key must include type and id.');
            }

            return new self((string) $key['type'], (string) $key['id']);
        }

        if ($owner instanceof Model) {
            $id = $owner->getKey();
            if ($id === null) {
                throw new InvalidArgumentException('Eloquent owner model must have a non-null key.');
            }

            return new self((string) $owner->getMorphClass(), (string) $id);
        }

        throw new InvalidArgumentException('Owner must be null, an Eloquent model, Owner, or implement SanvexOwner.');
    }

    public function type(): string
    {
        return $this->type;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function isGlobal(): bool
    {
        return $this->type === self::GLOBAL_TYPE && $this->id === self::GLOBAL_ID;
    }

    public function cacheKey(): string
    {
        return $this->type.'::'.$this->id;
    }

    /**
     * @return array{type:string,id:string}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'id' => $this->id,
        ];
    }
}
