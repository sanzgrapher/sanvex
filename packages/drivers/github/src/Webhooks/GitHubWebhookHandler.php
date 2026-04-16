<?php

namespace Sanvex\Drivers\GitHub\Webhooks;

use Sanvex\Core\DTOs\WebhookResult;

class GitHubWebhookHandler
{
    public function handle(array $headers, array|string $payload, callable $storeEntity): WebhookResult
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

        $eventType = is_array($normalizedHeaders['x-github-event'] ?? null)
            ? ($normalizedHeaders['x-github-event'][0] ?? 'unknown')
            : ($normalizedHeaders['x-github-event'] ?? 'unknown');

        $data = is_string($payload) ? json_decode($payload, true) : $payload;

        if (!is_array($data)) {
            return WebhookResult::fail('Invalid GitHub payload.', 400, 'github');
        }

        match ($eventType) {
            'push' => $this->handlePush($data, $storeEntity),
            'pull_request' => $this->handlePullRequest($data, $storeEntity),
            'issues' => $this->handleIssues($data, $storeEntity),
            'release' => $this->handleRelease($data, $storeEntity),
            default => null,
        };

        return WebhookResult::ok(['status' => 'ok'], 'github', $eventType);
    }

    private function handlePush(array $data, callable $storeEntity): void
    {
        $repoId = (string) ($data['repository']['id'] ?? uniqid());
        $storeEntity('repository', $repoId, $data['repository'] ?? []);
    }

    private function handlePullRequest(array $data, callable $storeEntity): void
    {
        $pr = $data['pull_request'] ?? [];
        $id = (string) ($pr['id'] ?? uniqid());
        $storeEntity('pull_request', $id, $pr);
    }

    private function handleIssues(array $data, callable $storeEntity): void
    {
        $issue = $data['issue'] ?? [];
        $id = (string) ($issue['id'] ?? uniqid());
        $storeEntity('issue', $id, $issue);
    }

    private function handleRelease(array $data, callable $storeEntity): void
    {
        $release = $data['release'] ?? [];
        $id = (string) ($release['id'] ?? uniqid());
        $storeEntity('release', $id, $release);
    }
}
