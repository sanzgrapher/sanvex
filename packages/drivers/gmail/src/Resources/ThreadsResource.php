<?php

namespace Sanvex\Drivers\Gmail\Resources;

use Sanvex\Core\BaseResource;

class ThreadsResource extends BaseResource
{
    private const BASE_URL = 'https://gmail.googleapis.com/gmail/v1/users/me';

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/threads', $args);
    }

    public function get(array $args): array
    {
        $id = $args['id'];
        return $this->driver->get(self::BASE_URL . "/threads/{$id}", $args);
    }
}
