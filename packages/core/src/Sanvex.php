<?php

namespace Sanvex\Core;

use Illuminate\Support\Facades\Facade;

class Sanvex extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sanvex.manager';
    }
}
