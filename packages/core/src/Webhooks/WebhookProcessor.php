<?php

namespace Sanvex\Core\Webhooks;

use Illuminate\Support\Facades\DB;
use Sanvex\Core\BaseDriver;
use Sanvex\Core\DTOs\WebhookResult;
use Sanvex\Core\Tenancy\Owner;

class WebhookProcessor
{
    public function process(array $headers, array $payload, array $drivers): WebhookResult
    {
        $driver = $this->identifyDriver($headers, $drivers);

        if (!$driver) {
            return WebhookResult::fail('Unable to identify driver from webhook headers.', 400);
        }

        try {
            $result = $driver->handleWebhook($headers, $payload);

            $this->logEvent($driver->id, $payload, $result);

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

    private function logEvent(string $driver, array $payload, WebhookResult $result): void
    {
        $owner = $result->owner ?? Owner::global();

        try {
            DB::table('sv_events')->insert([
                'owner_type' => $owner->type(),
                'owner_id' => $owner->id(),
                'driver' => $driver,
                'event_type' => $result->eventType ?? 'unknown',
                'payload' => json_encode($payload),
                'status' => $result->success ? 'processed' : 'failed',
                'error' => $result->error,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Non-fatal: logging failure should not break webhook processing.
            // Log to error channel so the failure is visible without halting the webhook.
            if (class_exists(\Illuminate\Support\Facades\Facade::class) && \Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                \Illuminate\Support\Facades\Log::error('sanvex webhook event log failed', [
                    'driver' => $driver,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
