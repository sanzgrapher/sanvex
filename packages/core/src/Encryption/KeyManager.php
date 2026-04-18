<?php

namespace Sanvex\Core\Encryption;

use Illuminate\Support\Facades\DB;

class KeyManager
{
    public function __construct(private readonly EncryptionService $encryption) {}

    public function storeCredential(string $driver, string $key, string $value): void
    {
        $dek = $this->encryption->generateDek();
        $encryptedDek = $this->encryption->encryptDek($dek);
        $encryptedValue = $this->encryption->encrypt($value, $dek);

        DB::table('sv_accounts')->updateOrInsert(
            ['driver' => $driver, 'key_name' => $key],
            [
                'encrypted_value' => $encryptedValue,
                'encrypted_dek' => $encryptedDek,
                'updated_at' => now(),
            ]
        );

        // Set created_at only on first insert (not on updates) via separate upsert logic
        DB::table('sv_accounts')
            ->where('driver', $driver)
            ->where('key_name', $key)
            ->whereNull('created_at')
            ->update(['created_at' => now()]);
    }

    public function getCredential(string $driver, string $key): ?string
    {
        $record = DB::table('sv_accounts')
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
