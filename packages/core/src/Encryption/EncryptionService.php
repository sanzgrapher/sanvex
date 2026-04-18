<?php

namespace Sanvex\Core\Encryption;

use Sanvex\Core\Exceptions\ConnectorException;

class EncryptionService
{
    private const CIPHER = 'AES-256-CBC';
    private const IV_LENGTH = 16;

    public function __construct(private readonly string $kek) {}

    public function generateDek(): string
    {
        return base64_encode(random_bytes(32));
    }

    public function encryptDek(string $dek): string
    {
        $key = base64_decode($this->kek, true);
        if ($key === false) {
            throw new ConnectorException('Invalid KEK encoding.');
        }
        $iv = random_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt($dek, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new ConnectorException('Failed to encrypt DEK.');
        }

        return base64_encode($iv . $encrypted);
    }

    public function decryptDek(string $encryptedDek): string
    {
        $key = base64_decode($this->kek, true);
        if ($key === false) {
            throw new ConnectorException('Invalid KEK encoding.');
        }
        $raw = base64_decode($encryptedDek, true);
        if ($raw === false) {
            throw new ConnectorException('Invalid encrypted DEK encoding.');
        }
        $iv = substr($raw, 0, self::IV_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH);
        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new ConnectorException('Failed to decrypt DEK.');
        }

        return $decrypted;
    }

    public function encrypt(string $data, string $dek): string
    {
        $key = base64_decode($dek, true);
        if ($key === false) {
            throw new ConnectorException('Invalid DEK encoding.');
        }
        $iv = random_bytes(self::IV_LENGTH);
        $encrypted = openssl_encrypt($data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($encrypted === false) {
            throw new ConnectorException('Failed to encrypt data.');
        }

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $data, string $dek): string
    {
        $key = base64_decode($dek, true);
        if ($key === false) {
            throw new ConnectorException('Invalid DEK encoding.');
        }
        $raw = base64_decode($data, true);
        if ($raw === false) {
            throw new ConnectorException('Invalid encrypted data encoding.');
        }
        $iv = substr($raw, 0, self::IV_LENGTH);
        $ciphertext = substr($raw, self::IV_LENGTH);
        $decrypted = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            throw new ConnectorException('Failed to decrypt data.');
        }

        return $decrypted;
    }
}
