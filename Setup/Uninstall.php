<?php

declare(strict_types=1);

namespace MagePulse\Collector\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Service\Key;

class Uninstall implements UninstallInterface
{
    protected ConfigProvider $configProvider;

    public function __construct(
        ConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $this->uninstallConfigData($setup);
    }

    private function uninstallConfigData(SchemaSetupInterface $setup): self
    {
        $configTable = $setup->getTable('core_config_data');
        $setup->getConnection()->delete($configTable, "`path` LIKE '".ConfigProvider::PATH_PREFIX."%'");

        return $this;
    }
}
