<?php

namespace Sanvex\Drivers\Notion;

use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\Auth\OAuthProviderConfig;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Drivers\Notion\Auth\NotionKeyBuilder;
use Sanvex\Drivers\Notion\Resources\DatabasesResource;
use Sanvex\Drivers\Notion\Resources\PagesResource;
use Sanvex\Drivers\Notion\Resources\SearchResource;

class NotionDriver extends BaseDriver
{
    public string $id = 'notion';

    public string $name = 'Notion';

    public array $authTypes = ['api_key', 'oauth_2'];

    public string $defaultAuthType = 'api_key';

    public function oauthConfig(): ?OAuthProviderConfig
    {
        return new OAuthProviderConfig(
            clientId: config('sanvex.driver_configs.notion.oauth.client_id', ''),
            clientSecret: config('sanvex.driver_configs.notion.oauth.client_secret', ''),
            authorizationUrl: 'https://api.notion.com/v1/oauth/authorize',
            tokenUrl: 'https://api.notion.com/v1/oauth/token',
            redirectUri: config('sanvex.driver_configs.notion.oauth.redirect_uri', config('app.url').'/sanvex/notion/callback'),
            scopes: []
        );
    }

    protected function defaultHeaders(): array
    {
        return [
            'Notion-Version' => '2022-06-28',
        ];
    }

    public function pages(): PagesResource
    {
        return new PagesResource($this);
    }

    public function databases(): DatabasesResource
    {
        return new DatabasesResource($this);
    }

    public function search(): SearchResource
    {
        return new SearchResource($this);
    }
    public function blocks(): Resources\BlocksResource
    {
        return new Resources\BlocksResource($this);
    }

    public function users(): Resources\UsersResource
    {
        return new Resources\UsersResource($this);
    }
    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        return WebhookResult::ok(['status' => 'ok'], 'notion', 'notification');
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        return true;
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new NotionKeyBuilder(
            driver: $this->id,
            keyManager: $this->keyManager,
            owner: $this->owner(),
        );
    }
}
