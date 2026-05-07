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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;
use MagePulse\Collector\Model\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    private ScopeConfigInterface $scopeConfig;
    private ModuleListInterface $moduleList;
    private EncryptorInterface $encryptor;
    private ConfigProvider $configProvider;

    protected function setUp(): void
    {
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->moduleList = $this->createMock(ModuleListInterface::class);
        $this->encryptor = $this->createMock(EncryptorInterface::class);

        $this->configProvider = new ConfigProvider(
            $this->scopeConfig,
            $this->moduleList,
            $this->encryptor
        );
    }

    public function testIsEnabledReturnsTrueWhenFlagIsSet(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/general/enabled', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('1');

        $this->assertTrue($this->configProvider->isEnabled());
    }

    public function testIsEnabledReturnsFalseWhenFlagIsNotSet(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/general/enabled', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('0');

        $this->assertFalse($this->configProvider->isEnabled());
    }

    public function testGetSiteLicenseReturnsConfiguredValue(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/general/site_license', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('abc123-license');

        $this->assertSame('abc123-license', $this->configProvider->getSiteLicense());
    }

    public function testGetSiteLicenseReturnsNullWhenNotSet(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/general/site_license', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('');

        $result = $this->configProvider->getSiteLicense();

        $this->assertEmpty($result);
    }

    public function testGetPrivateKeyDecryptsStoredValue(): void
    {
        $encryptedKey = 'encrypted:abc123';
        $decryptedKey = 'plain-private-key';

        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/keys/private_key', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn($encryptedKey);

        $this->encryptor->method('decrypt')
            ->with($encryptedKey)
            ->willReturn($decryptedKey);

        $this->assertSame($decryptedKey, $this->configProvider->getPrivateKey());
    }

    public function testGetPublicKeyReturnsConfiguredValue(): void
    {
        $this->scopeConfig->method('getValue')
            ->with('magepulse_collector/keys/public_key', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null)
            ->willReturn('hex-public-key');

        $this->assertSame('hex-public-key', $this->configProvider->getPublicKey());
    }

    public function testIsConfiguredReturnsTrueWhenBothKeysPresent(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnMap([
                ['magepulse_collector/general/site_license', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'some-license'],
                ['magepulse_collector/keys/private_key', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'encrypted-key'],
            ]);

        $this->encryptor->method('decrypt')->willReturn('decrypted-key');

        $this->assertTrue($this->configProvider->isConfigured());
    }

    public function testIsConfiguredReturnsFalseWhenSiteLicenseEmpty(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnMap([
                ['magepulse_collector/general/site_license', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, ''],
                ['magepulse_collector/keys/private_key', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'encrypted-key'],
            ]);

        $this->encryptor->method('decrypt')->willReturn('decrypted-key');

        $this->assertFalse($this->configProvider->isConfigured());
    }

    public function testIsConfiguredReturnsFalseWhenPrivateKeyEmpty(): void
    {
        $this->scopeConfig->method('getValue')
            ->willReturnMap([
                ['magepulse_collector/general/site_license', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, 'some-license'],
                ['magepulse_collector/keys/private_key', ScopeConfigInterface::SCOPE_TYPE_DEFAULT, null, ''],
            ]);

        $this->encryptor->method('decrypt')->willReturn('');

        $this->assertFalse($this->configProvider->isConfigured());
    }
}
