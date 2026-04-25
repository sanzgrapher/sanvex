<?php

namespace Sanvex\Core\Permissions;

use Illuminate\Support\Facades\DB;
use Sanvex\Core\Tenancy\Owner;

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
        string $mode,
        mixed $owner = null,
    ): bool|string {
        $resolvedOwner = Owner::resolve($owner);

        return match ($mode) {
            self::MODE_OPEN => true,
            self::MODE_READONLY => !in_array(strtolower($action), self::$writeActions, true),
            self::MODE_STRICT => $this->requireApproval($driver, $resource, $action, $args, $resolvedOwner),
            self::MODE_CAUTIOUS => $this->cautious($driver, $resource, $action, $args, $resolvedOwner),
            default => false,
        };
    }

    private function cautious(string $driver, string $resource, string $action, array $args, Owner $owner): bool|string
    {
        if (!in_array(strtolower($action), self::$writeActions, true)) {
            return true;
        }

        return $this->requireApproval($driver, $resource, $action, $args, $owner);
    }

    private function requireApproval(string $driver, string $resource, string $action, array $args, Owner $owner): string
    {
        $token = bin2hex(random_bytes(16));

        try {
            DB::table('sv_permissions')->insert([
                'owner_type' => $owner->type(),
                'owner_id' => $owner->id(),
                'driver' => $driver,
                'resource' => $resource,
                'action' => $action,
                'args' => json_encode($args),
                'approval_token' => $token,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Log the failure so operators know the permission system is degraded.
            if (class_exists(\Illuminate\Support\Facades\Facade::class) && \Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                \Illuminate\Support\Facades\Log::error('sanvex permission record failed to save', [
                    'driver' => $driver,
                    'action' => "{$resource}.{$action}",
                    'error' => $e->getMessage(),
                ]);
            }
            // Return URL anyway so the caller can still surface an approval request,
            // but the token won't be persisted — the approval flow will be unable to match it.
        }

        $baseUrl = config('sanvex.permissions.approval_url', '/sanvex/approve');

        return "{$baseUrl}?token={$token}";
    }
}
