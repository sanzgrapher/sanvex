<?php

namespace Sanvex\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Sanvex\Core\ConnectorManager;

class WebhookController extends Controller
{
    public function __construct(private readonly ConnectorManager $connector) {}

    public function handle(Request $request, ?string $tenantId = null): JsonResponse
    {
        $headers = $request->headers->all();
        $payload = $request->all();

        $result = $this->connector->processWebhook($headers, $payload, $tenantId);

        return response()->json(
            $result->response ?? ['status' => $result->success ? 'ok' : 'error', 'error' => $result->error],
            $result->status
        );
    }
}
