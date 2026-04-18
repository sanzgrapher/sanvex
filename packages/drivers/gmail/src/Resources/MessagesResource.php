<?php

namespace Sanvex\Drivers\Gmail\Resources;

use Sanvex\Core\BaseResource;

class MessagesResource extends BaseResource
{
    private const BASE_URL = 'https://gmail.googleapis.com/gmail/v1/users/me';

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/messages', $args);
    }

    public function get(array $args): array
    {
        $id = $args['id'];
        return $this->driver->get(self::BASE_URL . "/messages/{$id}", $args);
    }

    public function send(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/messages/send', $args);
    }

    public function delete(array $args): array
    {
        $id = $args['id'];
        return $this->driver->delete(self::BASE_URL . "/messages/{$id}");
    }
}
