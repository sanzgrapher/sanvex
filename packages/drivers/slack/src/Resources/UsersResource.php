<?php

namespace Sanvex\Drivers\Slack\Resources;

use Sanvex\Core\BaseResource;

class UsersResource extends BaseResource
{
    private const BASE_URL = 'https://slack.com/api';

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/users.list', $args);
    }

    public function info(array $args): array
    {
        return $this->driver->get(self::BASE_URL . '/users.info', $args);
    }

    public function lookupByEmail(array $args): array
    {
        return $this->driver->get(self::BASE_URL . '/users.lookupByEmail', $args);
    }
}
