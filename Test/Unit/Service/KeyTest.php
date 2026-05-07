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

namespace MagePulse\Collector\Test\Unit\Service;

use MagePulse\Collector\Service\Key;
use PHPUnit\Framework\TestCase;

class KeyTest extends TestCase
{
    private Key $key;

    protected function setUp(): void
    {
        $this->key = new Key();
        $this->key->createKeyPair();
    }

    public function testCreateKeyPairReturnsBinaryKeypair(): void
    {
        $key = new Key();
        $keypair = $key->createKeyPair();

        $this->assertIsString($keypair);
        $this->assertSame(SODIUM_CRYPTO_BOX_KEYPAIRBYTES, strlen($keypair));
    }

    public function testGetPublicKeyReturnsLowercaseHex(): void
    {
        $publicKey = $this->key->getPublicKey();

        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $publicKey);
    }

    public function testGetPublicKeyIsCorrectLength(): void
    {
        $publicKey = $this->key->getPublicKey();

        $this->assertSame(SODIUM_CRYPTO_BOX_PUBLICKEYBYTES * 2, strlen($publicKey));
    }

    public function testGetPrivateKeyReturnsLowercaseHex(): void
    {
        $privateKey = $this->key->getPrivateKey();

        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $privateKey);
    }

    public function testGetPrivateKeyIsCorrectLength(): void
    {
        $privateKey = $this->key->getPrivateKey();

        $this->assertSame(SODIUM_CRYPTO_BOX_SECRETKEYBYTES * 2, strlen($privateKey));
    }

    public function testKeypairIsUniquePerInstance(): void
    {
        $keyA = new Key();
        $keyA->createKeyPair();

        $keyB = new Key();
        $keyB->createKeyPair();

        $this->assertNotSame($keyA->getPublicKey(), $keyB->getPublicKey());
        $this->assertNotSame($keyA->getPrivateKey(), $keyB->getPrivateKey());
    }

    public function testGeneratedKeypairCanBeUsedForEncryption(): void
    {
        $senderKey = new Key();
        $senderKey->createKeyPair();
        $senderPrivHex = $senderKey->getPrivateKey();
        $senderPubHex = $senderKey->getPublicKey();

        $receiverKey = new Key();
        $receiverKey->createKeyPair();
        $receiverPrivHex = $receiverKey->getPrivateKey();
        $receiverPubHex = $receiverKey->getPublicKey();

        $encryptKeypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_hex2bin($senderPrivHex),
            sodium_hex2bin($receiverPubHex)
        );

        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        $message = 'test message';
        $ciphertext = sodium_crypto_box($message, $nonce, $encryptKeypair);

        $decryptKeypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_hex2bin($receiverPrivHex),
            sodium_hex2bin($senderPubHex)
        );
        $decrypted = sodium_crypto_box_open($ciphertext, $nonce, $decryptKeypair);

        $this->assertSame($message, $decrypted);
    }
}
