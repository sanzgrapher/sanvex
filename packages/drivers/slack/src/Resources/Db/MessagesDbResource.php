<?php

namespace Sanvex\Drivers\Slack\Resources\Db;

use Sanvex\Core\BaseDbResource;

class MessagesDbResource extends BaseDbResource
{
    protected string $entityType = 'message';
}
