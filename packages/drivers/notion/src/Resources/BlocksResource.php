<?php

namespace Sanvex\Drivers\Notion\Resources;

use Sanvex\Core\BaseResource;

class BlocksResource extends BaseResource
{
    private const BASE_URL = 'https://api.notion.com/v1';

    public function get(array $args): array
    {
        return $this->getManyChildBlocks($args);
    }

    public function retrieve(array $args): array
    {
        return $this->getManyChildBlocks($args);
    }

    public function list(array $args): array
    {
        return $this->getManyChildBlocks($args);
    }

    public function getManyChildBlocks(array $args): array
    {
        $id = $args['block_id'] ?? $args['id'] ?? $args['page_id'] ?? null;

        // If AI passes a full URL instead of an ID, extract the 32-character Notion ID
        if (is_string($id) && preg_match('/([a-f0-9]{32})(?:\?|$)/i', str_replace('-', '', $id), $matches)) {
            $id = $matches[1];
        }

        $params = [];

        if (isset($args['start_cursor'])) {
            $params['start_cursor'] = $args['start_cursor'];
        }

        if (isset($args['page_size'])) {
            $params['page_size'] = $args['page_size'];
        }

        $response = $this->driver->get(self::BASE_URL."/blocks/{$id}/children", $params);

        // Add AI-friendly text extraction to make it trivial for the agent to read blocks
        if (isset($response['results']) && is_array($response['results'])) {
            $extractedText = [];
            foreach ($response['results'] as &$block) {
                $type = $block['type'] ?? null;
                if ($type && isset($block[$type]['rich_text'])) {
                    $text = '';
                    foreach ($block[$type]['rich_text'] as $rt) {
                        $text .= $rt['plain_text'] ?? '';
                    }
                    $block['extracted_text'] = $text;
                    if (trim($text) !== '') {
                        // Prefix checkboxes to show their status
                        if ($type === 'to_do') {
                            $checked = isset($block['to_do']['checked']) && $block['to_do']['checked'] ? '[x]' : '[ ]';
                            $extractedText[] = "{$checked} {$text}";
                        } else {
                            $extractedText[] = "- {$text}";
                        }
                    }
                }
            }
            $response['sanvex_extracted_text'] = implode("\n", $extractedText);
        }

        return $response;
    }

    public function append(array $args): array
    {
        $id = $args['block_id'] ?? $args['id'] ?? $args['page_id'] ?? null;

        // Extract 32-char ID if full URL is passed
        if (is_string($id) && preg_match('/([a-f0-9]{32})(?:\?|$)/i', str_replace('-', '', $id), $matches)) {
            $id = $matches[1];
        }

        $data = ['children' => $args['children'] ?? []];

        // Handle simplified 'content' argument
        if (isset($args['content']) && empty($data['children'])) {
            $contentNode = $args['content'];
            $contentType = $contentNode['type'] ?? 'paragraph';
            $contentText = $contentNode['text']['content'] ?? (is_string($contentNode) ? $contentNode : '');

            if (! empty($contentText)) {
                $data['children'] = [
                    [
                        'object' => 'block',
                        'type' => $contentType,
                        $contentType => [
                            'rich_text' => [
                                [
                                    'type' => 'text',
                                    'text' => [
                                        'content' => $contentText,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            }
        }

        return $this->driver->patch(self::BASE_URL."/blocks/{$id}/children", $data);
    }

    public function update(array $args): array
    {
        return $this->append($args);
    }
}
