<?php

namespace Sanvex\Drivers\GitHub\Resources;

use Sanvex\Core\BaseResource;

class RepositoriesResource extends BaseResource
{
    private const BASE_URL = 'https://api.github.com';

    public function get(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->get(self::BASE_URL . "/repos/{$owner}/{$repo}");
    }

    public function list(array $args = []): array
    {
        return $this->driver->get(self::BASE_URL . '/user/repos', $args);
    }

    public function create(array $args): array
    {
        $org = $args['org'] ?? null;
        $url = $org
            ? self::BASE_URL . "/orgs/{$org}/repos"
            : self::BASE_URL . '/user/repos';
        return $this->driver->post($url, $args);
    }

    public function delete(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->delete(self::BASE_URL . "/repos/{$owner}/{$repo}");
    }
}
