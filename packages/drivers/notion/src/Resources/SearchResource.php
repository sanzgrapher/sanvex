<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class SearchResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    /**
     * Search across all pages and databases in the Notion workspace.
     */
    public function search(array $args = []): array
    {
        return $this->driver->post(self::BASE_URL . '/search', $args);
    }
    
    /**
     * Alias for search to support 'list' actions
     */
    public function list(array $args = []): array
    {
        return $this->search($args);
    }
}
