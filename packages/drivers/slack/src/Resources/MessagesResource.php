<?php

namespace Sanvex\Drivers\Slack\Resources;

use Sanvex\Core\BaseResource;

class MessagesResource extends BaseResource
{
    private const BASE_URL = 'https://slack.com/api';

    public function post(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/chat.postMessage', $args);
    }

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/conversations.history', $args);
    }

    public function delete(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/chat.delete', $args);
    }

    public function update(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/chat.update', $args);
    }
}
