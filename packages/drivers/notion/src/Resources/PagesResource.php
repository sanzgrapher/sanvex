<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class PagesResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    public function get(array $args): array
    {
        $id = $args['id'];
        return $this->driver->get(self::BASE_URL . "/pages/{$id}");
    }

    public function create(array $args): array
    {
        return $this->driver->post(self::BASE_URL . '/pages', $args);
    }

    public function update(array $args): array
    {
        $id = $args['id'];
        return $this->driver->put(self::BASE_URL . "/pages/{$id}", $args);
    }
}
