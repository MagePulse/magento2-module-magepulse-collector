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

namespace MagePulse\Collector\Setup;

use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use SodiumException;
use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Service\Key;

class InstallData implements InstallDataInterface
{
    protected ConfigProvider $configProvider;
    protected WriterInterface $configWriter;
    protected EncryptorInterface $encryptor;

    public function __construct(
        ConfigProvider $configProvider,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor,
    ) {
        $this->configProvider = $configProvider;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
    }

    /**
     * @throws SodiumException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        // Check the key is generated and generate if not
        if ($this->configProvider->getPublicKey() === '' || $this->configProvider->getPrivateKey() === '') {
            $key = new Key();
            $key->createKeyPair();

            $this->configWriter->save(
                ConfigProvider::PATH_PREFIX . ConfigProvider::PRIVATE_KEY,
                $this->encryptor->encrypt($key->getPrivateKey()),
                'default',
                0
            );

            $this->configWriter->save(
                ConfigProvider::PATH_PREFIX . ConfigProvider::PUBLIC_KEY,
                $key->getPublicKey(),
                'default',
                0
            );
        }
    }
}
