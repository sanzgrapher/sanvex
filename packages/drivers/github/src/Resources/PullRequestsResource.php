<?php

namespace Sanvex\Drivers\GitHub\Resources;

use Sanvex\Core\BaseResource;

class PullRequestsResource extends BaseResource
{
    private const BASE_URL = 'https://api.github.com';

    public function list(array $args = []): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->get(self::BASE_URL . "/repos/{$owner}/{$repo}/pulls", $args);
    }

    public function get(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        $number = $args['number'];
        return $this->driver->get(self::BASE_URL . "/repos/{$owner}/{$repo}/pulls/{$number}");
    }

    public function create(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        return $this->driver->post(self::BASE_URL . "/repos/{$owner}/{$repo}/pulls", $args);
    }

    public function merge(array $args): array
    {
        $owner = $args['owner'];
        $repo = $args['repo'];
        $number = $args['number'];
        return $this->driver->put(
            self::BASE_URL . "/repos/{$owner}/{$repo}/pulls/{$number}/merge",
            $args
        );
    }
}
