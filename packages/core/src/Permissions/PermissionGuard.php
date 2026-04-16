<?php

namespace Sanvex\Core\Permissions;

use Illuminate\Support\Facades\DB;

class PermissionGuard
{
    public const MODE_CAUTIOUS = 'cautious';
    public const MODE_STRICT = 'strict';
    public const MODE_OPEN = 'open';
    public const MODE_READONLY = 'readonly';

    private static array $writeActions = ['create', 'update', 'delete', 'post', 'send'];

    public function check(
        string $driver,
        string $resource,
        string $action,
        array $args,
        string $mode
    ): bool|string {
        return match ($mode) {
            self::MODE_OPEN => true,
            self::MODE_READONLY => !in_array(strtolower($action), self::$writeActions, true),
            self::MODE_STRICT => $this->requireApproval($driver, $resource, $action, $args),
            self::MODE_CAUTIOUS => $this->cautious($driver, $resource, $action, $args),
            default => false,
        };
    }

    private function cautious(string $driver, string $resource, string $action, array $args): bool|string
    {
        if (!in_array(strtolower($action), self::$writeActions, true)) {
            return true;
        }

        return $this->requireApproval($driver, $resource, $action, $args);
    }

    private function requireApproval(string $driver, string $resource, string $action, array $args): string
    {
        $token = bin2hex(random_bytes(16));

        try {
            DB::table('sv_permissions')->insert([
                'driver' => $driver,
                'resource' => $resource,
                'action' => $action,
                'args' => json_encode($args),
                'approval_token' => $token,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable) {
            // If DB not available, return URL anyway
        }

        $baseUrl = config('sanvex.permissions.approval_url', '/sanvex/approve');

        return "{$baseUrl}?token={$token}";
    }
}
