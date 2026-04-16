<?php

namespace Sanvex\Core\Webhooks;

use Illuminate\Support\Facades\DB;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;

class WebhookProcessor
{
    public function process(array $headers, array $payload, ?string $tenantId, array $drivers): WebhookResult
    {
        $driver = $this->identifyDriver($headers, $drivers);

        if (!$driver) {
            return WebhookResult::fail('Unable to identify driver from webhook headers.', 400);
        }

        try {
            $result = $driver->handleWebhook($headers, $payload);

            $this->logEvent($driver->id, $tenantId, $payload, $result);

            return $result;
        } catch (\Throwable $e) {
            return WebhookResult::fail($e->getMessage(), 500, $driver->id);
        }
    }

    private function identifyDriver(array $headers, array $drivers): ?BaseDriver
    {
        $normalizedHeaders = array_change_key_case($headers, CASE_LOWER);

        if (isset($normalizedHeaders['x-slack-signature'])) {
            return $drivers['slack'] ?? null;
        }

        if (isset($normalizedHeaders['x-github-event'])) {
            return $drivers['github'] ?? null;
        }

        if (isset($normalizedHeaders['linear-delivery'])) {
            return $drivers['linear'] ?? null;
        }

        return null;
    }

    private function logEvent(string $driver, ?string $tenantId, array $payload, WebhookResult $result): void
    {
        try {
            DB::table('sv_events')->insert([
                'driver' => $driver,
                'tenant_id' => $tenantId ?? 'default',
                'event_type' => $result->eventType ?? 'unknown',
                'payload' => json_encode($payload),
                'status' => $result->success ? 'processed' : 'failed',
                'error' => $result->error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // Non-fatal: logging failure should not break webhook processing
        }
    }
}
