<?php

namespace Sanvex\Core\Encryption;

use Illuminate\Support\Facades\DB;

class KeyManager
{
    public function __construct(private readonly EncryptionService $encryption) {}

    public function storeCredential(string $tenantId, string $driver, string $key, string $value): void
    {
        $dek = $this->encryption->generateDek();
        $encryptedDek = $this->encryption->encryptDek($dek);
        $encryptedValue = $this->encryption->encrypt($value, $dek);

        DB::table('sv_accounts')->updateOrInsert(
            ['tenant_id' => $tenantId, 'driver' => $driver, 'key_name' => $key],
            [
                'encrypted_value' => $encryptedValue,
                'encrypted_dek' => $encryptedDek,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function getCredential(string $tenantId, string $driver, string $key): ?string
    {
        $record = DB::table('sv_accounts')
            ->where('tenant_id', $tenantId)
            ->where('driver', $driver)
            ->where('key_name', $key)
            ->first();

        if (!$record) {
            return null;
        }

        $dek = $this->encryption->decryptDek($record->encrypted_dek);

        return $this->encryption->decrypt($record->encrypted_value, $dek);
    }
}
