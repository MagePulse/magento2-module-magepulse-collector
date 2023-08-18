<?php

declare(strict_types=1);

namespace MagePulse\Collector\Model;

use Exception;
use SodiumException;

class Encryptor
{
    private ConfigProvider $configProvider;

    public function __construct(ConfigProvider $configProvider)
    {
        $this->configProvider = $configProvider;
    }

    /**
     * Encrypt the data ready for transmission
     *
     * @param string $data
     * @return string
     * @throws SodiumException
     */
    public function encrypt(string $data): string
    {
        return $this->generateCipherText($data);
    }

    /**
     * @throws SodiumException
     * @throws Exception
     */
    private function generateCipherText($message): string
    {
        $key = $this->generateKey();
        $nonce = $this->generateNonce();
        $cipherText = sodium_crypto_secretbox($message, $nonce, $key);
        sodium_memzero($key);
        sodium_memzero($message);
        $result = sodium_bin2base64($nonce . $cipherText, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        sodium_memzero($nonce);

        return $result;
    }

    /**
     * @throws SodiumException
     */
    private function generateKey(): string
    {
        // Determine if there is a license installed yet
        if (!$this->configProvider->getSiteLicense()) {
            return sodium_base642bin($this->configProvider->getPrivateKey(), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        }

        return sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_base642bin($this->configProvider->getPrivateKey(), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING),
            sodium_base642bin($this->configProvider->getSiteLicense(), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING)
        );
    }

    /**
     * Generate a nonce for the encryption process this must be unique for each encryption
     *
     * @throws Exception
     */
    private function generateNonce(): bool|string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }
}
