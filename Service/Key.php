<?php

declare(strict_types=1);

namespace MagePulse\Collector\Service;

class Key
{
    private string $keyPair;

    /**
     * @throws \SodiumException
     */
    public function createKeyPair(): string
    {
        return $this->keyPair = sodium_crypto_box_keypair();
    }

    /**
     * @throws \SodiumException
     */
    public function getPublicKey(): string
    {
        return sodium_bin2base64(sodium_crypto_box_publickey($this->keyPair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }

    /**
     * @throws \SodiumException
     */
    public function getPrivateKey(): string
    {
        return sodium_bin2base64(sodium_crypto_box_secretkey($this->keyPair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
    }
}
