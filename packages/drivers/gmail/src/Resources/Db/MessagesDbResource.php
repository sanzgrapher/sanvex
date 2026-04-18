<?php

namespace Sanvex\Drivers\Gmail\Resources\Db;

use Sanvex\Core\BaseDbResource;

class MessagesDbResource extends BaseDbResource
{
    protected string $entityType = 'email_message';
}
