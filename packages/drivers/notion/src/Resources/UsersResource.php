<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class UsersResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    public function retrieve(array $args): array
    {
        return $this->get($args);
    }
    
    public function get(array $args): array
    {
        $id = $args['user_id'] ?? $args['id'] ?? null;
        return $this->driver->get(self::BASE_URL . "/users/{$id}");
    }

    public function list(array $args = []): array
    {
        $params = [];
        
        if (isset($args['start_cursor'])) {
            $params['start_cursor'] = $args['start_cursor'];
        }
        
        if (isset($args['page_size'])) {
            $params['page_size'] = $args['page_size'];
        }

        return $this->driver->get(self::BASE_URL . '/users', $params);
    }
    
    public function search(array $args = []): array
    {
        return $this->list($args);
    }
}
