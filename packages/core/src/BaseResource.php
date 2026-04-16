<?php

namespace Sanvex\Core;

abstract class BaseResource
{
    public function __construct(protected readonly BaseDriver $driver) {}
}
