<?php

namespace Sanvex\Drivers\Linear\Resources;

use Sanvex\Core\BaseResource;

class ProjectsResource extends BaseResource
{
    private const BASE_URL = 'https://api.linear.app/graphql';

    public function list(array $args = []): array
    {
        $query = '{ projects { nodes { id name description } } }';
        return $this->driver->post(self::BASE_URL, ['query' => $query, 'variables' => $args]);
    }

    public function get(array $args): array
    {
        $id = $args['id'];
        $query = "{ project(id: \"{$id}\") { id name description } }";
        return $this->driver->post(self::BASE_URL, ['query' => $query]);
    }
}
