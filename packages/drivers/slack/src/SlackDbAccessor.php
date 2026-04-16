<?php

namespace Sanvex\Drivers\Slack;

use Sanvex\Core\DbAccessor;
use Sanvex\Drivers\Slack\Resources\Db\ChannelsDbResource;
use Sanvex\Drivers\Slack\Resources\Db\MessagesDbResource;

class SlackDbAccessor extends DbAccessor
{
    public function messages(): MessagesDbResource
    {
        return new MessagesDbResource($this->driver);
    }

    public function channels(): ChannelsDbResource
    {
        return new ChannelsDbResource($this->driver);
    }
}
