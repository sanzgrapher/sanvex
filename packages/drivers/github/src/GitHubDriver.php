<?php

namespace Sanvex\Drivers\GitHub;

use Sanvex\Core\Auth\KeyBuilder;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Drivers\GitHub\Auth\GitHubKeyBuilder;
use Sanvex\Drivers\GitHub\Resources\IssuesResource;
use Sanvex\Drivers\GitHub\Resources\PullRequestsResource;
use Sanvex\Drivers\GitHub\Resources\RepositoriesResource;
use Sanvex\Drivers\GitHub\Webhooks\GitHubWebhookHandler;

class GitHubDriver extends BaseDriver
{
    public string $id = 'github';
    public string $name = 'GitHub';
    public array $authTypes = ['api_key', 'oauth2'];
    public string $defaultAuthType = 'api_key';

    public function repositories(): RepositoriesResource
    {
        return new RepositoriesResource($this);
    }

    public function issues(): IssuesResource
    {
        return new IssuesResource($this);
    }

    public function pullRequests(): PullRequestsResource
    {
        return new PullRequestsResource($this);
    }

    public function db(): GitHubDbAccessor
    {
        return new GitHubDbAccessor($this);
    }

    public function handleWebhook(array $headers, array|string $payload): WebhookResult
    {
        $handler = new GitHubWebhookHandler();
        return $handler->handle($headers, $payload, fn($type, $id, $data) => $this->storeEntity($type, $id, $data));
    }

    public function verifySignature(array $headers, string $rawBody, string $secret): bool
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

        $signature = is_array($normalizedHeaders['x-hub-signature-256'] ?? null)
            ? ($normalizedHeaders['x-hub-signature-256'][0] ?? '')
            : ($normalizedHeaders['x-hub-signature-256'] ?? '');

        $expected = 'sha256=' . hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }

    protected function makeKeyBuilder(): KeyBuilder
    {
        return new GitHubKeyBuilder(
            driver: $this->id,
            keyManager: $this->keyManager,
        );
    }
}
