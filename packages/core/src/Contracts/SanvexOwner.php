<?php

namespace Sanvex\Core\Contracts;

interface SanvexOwner
{
    /**
     * Return a stable polymorphic owner key.
     *
     * @return array{type:string,id:string|int}
     */
    public function sanvexOwnerKey(): array;
}
