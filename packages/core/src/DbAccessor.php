<?php

namespace Sanvex\Core;

class DbAccessor
{
    public function __construct(protected readonly BaseDriver $driver) {}
}
