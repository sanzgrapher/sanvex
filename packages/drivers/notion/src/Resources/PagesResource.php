<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class PagesResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    public function get(array $args): array
    {
        $id = $args['id'] ?? $args['page_id'] ?? null;

        // If AI passes a full URL instead of an ID, extract the 32-character Notion ID
        if (is_string($id) && preg_match('/([a-f0-9]{32})(?:\?|$)/i', str_replace('-', '', $id), $matches)) {
            $id = $matches[1];
        }

        return $this->driver->get(self::BASE_URL . "/pages/{$id}");
    }

    public function retrieve(array $args): array
    {
        return $this->get($args);
    }

    public function list(array $args = []): array
    {
        // Intercept AI asking to list pages and use the search endpoint instead
        return $this->driver->search()->search([
            'filter' => ['value' => 'page', 'property' => 'object'],
            'page_size' => $args['page_size'] ?? 100
        ]);
    }

    public function create(array $args): array
    {
        // Check if the AI provided a fake dummy parent ID because it didn't know one
        $isDummyParent = false;
        if (isset($args['parent'])) {
            $parentId = $args['parent']['database_id'] ?? $args['parent']['page_id'] ?? '';
            $isDummyParent = empty($parentId) || str_contains((string)$parentId, 'your_') || str_contains((string)$parentId, 'selected_') || str_contains((string)$parentId, 'dummy');
        }

        // If the AI/user tries to create a page but didn't provide a parent context,
        // we'll automatically search the workspace for a default parent (first accessible page).
        if (!isset($args['parent']) || $isDummyParent) {
            $searchResource = $this->driver->search();
            $results = $searchResource->search([
                'filter' => ['value' => 'page', 'property' => 'object'],
                'page_size' => 1
            ]);
            
            if (empty($results['results'])) {
                // If no page found, try for a database
                $results = $searchResource->search([
                    'filter' => ['value' => 'database', 'property' => 'object'],
                    'page_size' => 1
                ]);
                
                if (!empty($results['results'])) {
                    $args['parent'] = [
                        'type' => 'database_id',
                        'database_id' => $results['results'][0]['id']
                    ];
                }
            } else {
                $args['parent'] = [
                    'type' => 'page_id',
                    'page_id' => $results['results'][0]['id']
                ];
            }
        }
        
        // Ensure proper properties format if simple 'title' string is passed
        if (isset($args['title']) && !isset($args['properties']['title'])) {
            $args['properties'] = $args['properties'] ?? [];
            $args['properties']['title'] = [
                'title' => [
                    [
                        'text' => [
                            'content' => $args['title']
                        ]
                    ]
                ]
            ];
            unset($args['title']);
        }

        // Handle simplified 'content' argument from AI
        if (isset($args['content']) && !isset($args['children'])) {
            $contentNode = $args['content'];
            $contentType = $contentNode['type'] ?? 'paragraph';
            $contentText = $contentNode['text']['content'] ?? (is_string($contentNode) ? $contentNode : '');
            
            if (!empty($contentText)) {
                $args['children'] = [
                    [
                        'object' => 'block',
                        'type' => $contentType,
                        $contentType => [
                            'rich_text' => [
                                [
                                    'type' => 'text',
                                    'text' => [
                                        'content' => $contentText
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
            unset($args['content']);
        }

        return $this->driver->post(self::BASE_URL . '/pages', $args);
    }

    public function update(array $args): array
    {
        $id = $args['id'];
        return $this->driver->put(self::BASE_URL . "/pages/{$id}", $args);
    }
}
