<?php

namespace Sanvex\Drivers\Linear\Resources;

use Sanvex\Core\BaseResource;

class IssuesResource extends BaseResource
{
    private const BASE_URL = 'https://api.linear.app/graphql';

    public function list(array $args = []): array
    {
        $query = '{ issues { nodes { id title state { name } } } }';
        return $this->driver->post(self::BASE_URL, ['query' => $query, 'variables' => $args]);
    }

    public function get(array $args): array
    {
        $query = '{ issue(id: $id) { id title description state { name } } }';
        return $this->driver->post(self::BASE_URL, [
            'query' => $query,
            'variables' => ['id' => $args['id']],
        ]);
    }

    public function create(array $args): array
    {
        $mutation = 'mutation CreateIssue($input: IssueCreateInput!) { issueCreate(input: $input) { success issue { id title } } }';
        return $this->driver->post(self::BASE_URL, ['query' => $mutation, 'variables' => ['input' => $args]]);
    }

    public function update(array $args): array
    {
        $id = $args['id'];
        $mutation = 'mutation UpdateIssue($id: String!, $input: IssueUpdateInput!) { issueUpdate(id: $id, input: $input) { success issue { id title } } }';
        return $this->driver->post(self::BASE_URL, ['query' => $mutation, 'variables' => ['id' => $id, 'input' => $args]]);
    }
}
