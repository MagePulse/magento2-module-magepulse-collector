<?php

declare(strict_types=1);

namespace MagePulse\Collector\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Module\ModuleListInterface;
use MagePulse\Core\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    protected string $pathPrefix = 'magepulse_collector/';
    public const PATH_PREFIX = 'magepulse_collector/';

    public const ENABLED = 'general/enabled';
    public const SITE_LICENSE = 'general/site_license';

    public const PRIVATE_KEY = 'keys/private_key';
    public const PUBLIC_KEY = 'keys/public_key';

    private EncryptorInterface $encryptor;

    public function __construct(ScopeConfigInterface $scopeConfig, ModuleListInterface $moduleList, EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
        parent::__construct($scopeConfig, $moduleList);
    }

    public function isEnabled($storeId = null): bool
    {
        return (bool)$this->getValue(self::ENABLED, $storeId);
    }

    public function getSiteLicense($storeId = null): ?string
    {
        return $this->getValue(self::SITE_LICENSE, $storeId);
    }

    public function getPrivateKey($storeId = null): string
    {
        return $this->encryptor->decrypt($this->getValue(self::PRIVATE_KEY, $storeId));
    }

    public function getPublicKey($storeId = null): string
    {
        return $this->getValue(self::PUBLIC_KEY, $storeId);
    }
}
