<?php

namespace Sanvex\Drivers\GitHub\Resources;

use Sanvex\Core\BaseResource;

class IssuesResource extends BaseResource
{
    private const BASE_URL = 'https://api.github.com';

    public function list(array $args = []): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->get(self::BASE_URL . "/repos/{$owner}/{$repo}/issues", $args);
    }

    public function get(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        $number = $args['number'];
        return $this->driver->get(self::BASE_URL . "/repos/{$owner}/{$repo}/issues/{$number}");
    }

    public function create(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->post(self::BASE_URL . "/repos/{$owner}/{$repo}/issues", $args);
    }

    public function update(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        $number = $args['number'];
        return $this->driver->put(self::BASE_URL . "/repos/{$owner}/{$repo}/issues/{$number}", $args);
    }

    public function close(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        $number = $args['number'];
        return $this->driver->put(
            self::BASE_URL . "/repos/{$owner}/{$repo}/issues/{$number}",
            array_merge($args, ['state' => 'closed'])
        );
    }
}
