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

namespace MagePulse\Collector\Test\Unit\Model;

use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Model\Encryptor;
use PHPUnit\Framework\TestCase;

class EncryptorTest extends TestCase
{
    private string $senderKeypair;
    private string $receiverKeypair;
    private ConfigProvider $configProviderMock;
    private Encryptor $encryptor;

    protected function setUp(): void
    {
        $this->senderKeypair = sodium_crypto_box_keypair();
        $this->receiverKeypair = sodium_crypto_box_keypair();

        $senderPrivKeyHex = sodium_bin2hex(sodium_crypto_box_secretkey($this->senderKeypair));
        $receiverPubKeyHex = sodium_bin2hex(sodium_crypto_box_publickey($this->receiverKeypair));

        $this->configProviderMock = $this->createMock(ConfigProvider::class);
        $this->configProviderMock->method('getPrivateKey')->willReturn($senderPrivKeyHex);
        $this->configProviderMock->method('getSiteLicense')->willReturn($receiverPubKeyHex);

        $this->encryptor = new Encryptor($this->configProviderMock);
    }

    public function testEncryptReturnsHexDotHexFormat(): void
    {
        $result = $this->encryptor->encrypt('test message');
        $parts = explode('.', $result);

        $this->assertCount(2, $parts);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $parts[0]);
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $parts[1]);
    }

    public function testEncryptDecryptRoundTrip(): void
    {
        $message = 'hello world';
        $encrypted = $this->encryptor->encrypt($message);

        [$nonceHex, $ciphertextHex] = explode('.', $encrypted);
        $nonce = sodium_hex2bin($nonceHex);
        $ciphertext = sodium_hex2bin($ciphertextHex);

        $decryptKeypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_crypto_box_secretkey($this->receiverKeypair),
            sodium_crypto_box_publickey($this->senderKeypair)
        );
        $decrypted = sodium_crypto_box_open($ciphertext, $nonce, $decryptKeypair);

        $this->assertSame($message, $decrypted);
    }

    public function testNonceIsUniquePerEncryption(): void
    {
        $nonce1 = explode('.', $this->encryptor->encrypt('message'))[0];
        $nonce2 = explode('.', $this->encryptor->encrypt('message'))[0];

        $this->assertNotSame($nonce1, $nonce2);
    }

    public function testNonceIsCorrectLength(): void
    {
        $nonceHex = explode('.', $this->encryptor->encrypt('test'))[0];
        $nonce = sodium_hex2bin($nonceHex);

        $this->assertSame(SODIUM_CRYPTO_BOX_NONCEBYTES, strlen($nonce));
    }

    public function testEncryptProducesDifferentCiphertextsForSameMessage(): void
    {
        $ciphertext1 = explode('.', $this->encryptor->encrypt('same message'))[1];
        $ciphertext2 = explode('.', $this->encryptor->encrypt('same message'))[1];

        $this->assertNotSame($ciphertext1, $ciphertext2);
    }
}
