<?php

namespace Sanvex\Core;

use Illuminate\Support\Facades\Facade;

class ConnectorFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sanvex.connector';
    }
}
