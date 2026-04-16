<?php

namespace Sanvex\Drivers\Slack\Resources;

use Sanvex\Core\BaseResource;

class ChannelsResource extends BaseResource
{
    private const BASE_URL = 'https://slack.com/api';

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/conversations.list', $args);
    }

    public function info(array $args): array
    {
        return $this->driver->get(self::BASE_URL . '/conversations.info', $args);
    }

    public function join(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/conversations.join', $args);
    }
}
