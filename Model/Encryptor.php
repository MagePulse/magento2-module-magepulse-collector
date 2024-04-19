<?php
/*
 * MagePulse
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MagePulse Proprietary EULA
 * that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * https://magepulse.com/legal/magento-license/
 *
 * @category    MagePulse
 * @package     MagePulse_Collector
 * @copyright   Copyright (c) MagePulse (https://magepulse.com)
 * @license     https://magepulse.com/legal/magento-license/  MagePulse Proprietary EULA
 *
 */

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
        $cipherText = sodium_crypto_box($message, $nonce, $key);
        sodium_memzero($key);
        sodium_memzero($message);

        $result = sprintf('%s.%s', sodium_bin2hex($nonce), sodium_bin2hex($cipherText));

        sodium_memzero($nonce);

        return $result;
    }

    /**
     * @throws SodiumException
     */
    private function generateKey(): string
    {
        return sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_hex2bin($this->configProvider->getPrivateKey()),
            sodium_hex2bin($this->configProvider->getSiteLicense())
        );
    }

    /**
     * Generate a nonce for the encryption process this must be unique for each encryption
     *
     * @throws Exception
     */
    private function generateNonce(): string
    {
        return random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }
}
