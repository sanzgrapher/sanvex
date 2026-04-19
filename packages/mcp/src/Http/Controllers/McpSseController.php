<?php

namespace Sanvex\Mcp\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Sanvex\Mcp\Server\JsonRpcServer;
use Sanvex\Core\SanvexManager;
use Symfony\Component\HttpFoundation\StreamedResponse;

class McpSseController extends Controller
{
    /**
     * Connect to the MCP Server over HTTP (Server-Sent Events)
     */
    public function connect(Request $request): StreamedResponse
    {
        $sessionId = uniqid('mcp_', true);
        
        // Ensure standard PHP timeout doesn't kill the stream instantly
        set_time_limit(0);

        return response()->stream(function () use ($sessionId) {
            $this->sendSseMessage('endpoint', url("/sanvex/mcp/message?sessionId={$sessionId}"));

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                // Check for new messages placed in the cache by the POST /message endpoint
                $messageKey = "mcp_response_{$sessionId}";
                $message = Cache::pull($messageKey);

                if ($message) {
                    $this->sendSseMessage('message', $message);
                }

                flush();
                usleep(500000); // 500ms
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Receive JSON-RPC messages from the AI Client and process them
     */
    public function message(Request $request, SanvexManager $manager)
    {
        $sessionId = $request->query('sessionId');
        
        if (!$sessionId) {
            return response()->json(['error' => 'Missing sessionId'], 400);
        }

        $payload = $request->getContent();
        
        $server = new JsonRpcServer($manager);
        $response = $server->handle($payload);

        if ($response) {
            // Push the response to the SSE stream via the Laravel Cache
            Cache::put("mcp_response_{$sessionId}", $response, 60);
        }

        return response('Accepted', 202);
    }

    private function sendSseMessage(string $event, string $data): void
    {
        echo "event: {$event}\n";
        echo "data: {$data}\n\n";
        flush();
    }
}
