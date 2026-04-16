<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class DatabasesResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    public function query(array $args): array
    {
        $id = $args['database_id'];
        return $this->driver->post(self::BASE_URL . "/databases/{$id}/query", $args);
    }

    public function list(array $args = []): array
    {
        return $this->driver->post(self::BASE_URL . '/search', array_merge($args, ['filter' => ['value' => 'database', 'property' => 'object']]));
    }
}
