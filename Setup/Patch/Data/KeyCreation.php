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

namespace MagePulse\Collector\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use SodiumException;
use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Service\Key;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class KeyCreation implements
    DataPatchInterface,
    PatchRevertableInterface
{
    private ModuleDataSetupInterface $moduleDataSetup;

    protected ConfigProvider $configProvider;
    protected WriterInterface $configWriter;
    protected EncryptorInterface $encryptor;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ConfigProvider $configProvider
     * @param WriterInterface $configWriter
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ConfigProvider           $configProvider,
        WriterInterface          $configWriter,
        EncryptorInterface       $encryptor
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configProvider = $configProvider;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
    }

    /**
     * Do Upgrade
     *
     * @return void
     * @throws SodiumException
     */
    public function apply()
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

    /**
     * @inheritdoc
     */
    public function revert()
    {
//        $configTable = 'core_config_data';
//        $setup->getConnection()->delete($configTable, "`path` LIKE '".ConfigProvider::PATH_PREFIX."%'");
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
