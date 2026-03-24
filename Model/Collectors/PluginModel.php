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

namespace MagePulse\Collector\Model\Collectors;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Module\ModuleListInterface;
use MagePulse\Collector\Model\ModuleMetaInfo;
use Psr\Log\LoggerInterface;

class PluginModel implements CollectorInterface
{
    private ModuleListInterface $moduleList;
    private FullModuleList $fullModuleList;
    private ModuleManager $moduleManager;
    private ModuleMetaInfo $moduleMetaInfo;
    private Reader $moduleDirReader;
    private LoggerInterface $logger;

    public function __construct(
        FullModuleList $fullModuleList,
        ModuleListInterface $moduleList,
        ModuleManager $moduleManager,
        ModuleMetaInfo $moduleMetaInfo,
        Reader $moduleDirReader,
        LoggerInterface $logger
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->moduleMetaInfo = $moduleMetaInfo;
        $this->moduleDirReader = $moduleDirReader;
        $this->logger = $logger;
    }

    public function getData(): array
    {
        return $this->getModules();
    }

    /**
     * Get the list of modules
     * @return array
     */
    protected function getModules(): array
    {
        $modules = [];
        foreach ($this->fullModuleList->getAll() as $module) {
            $moduleName = $module['name'];
            $composerName = $this->getComposerName($moduleName);

            $modules[] = [
                'name' => $moduleName,
                'composer_name' => $composerName,
                'composer_version' => $this->moduleMetaInfo->getModuleMeta($moduleName)['version'] ?? '0.0.0',
                'module_version' => $this->getVersion($moduleName),
                'enabled' => $this->getStatus($moduleName),
                'license' => $this->moduleMetaInfo->getModuleMeta($moduleName)['license'] ?? 'N/A',
                'support' => $this->moduleMetaInfo->getModuleMeta($moduleName)['support'] ?? 'N/A',
            ];
        }

        return $modules;
    }

    /**
     * Get the composer name of a module
     * @param string $moduleName
     * @return string
     */
    protected function getComposerName(string $moduleName): string
    {
        try {
            $dir = $this->moduleDirReader->getModuleDir('', $moduleName);
            $composerJson = $dir . '/composer.json';
            if (file_exists($composerJson)) {
                $data = json_decode(file_get_contents($composerJson), true);
                return $data['name'] ?? 'N/A';
            }
        } catch (\Exception $e) {
            $this->logger->error('MagePulse Collector: failed to read composer.json for ' . $moduleName, ['exception' => $e]);
        }
        return 'N/A';
    }

    /**
     * Get the version of a module
     * @param $moduleName
     * @return string|null
     */
    protected function getVersion($moduleName): ?string
    {
        $version = '0.0.0';
        $module = $this->moduleList->getOne($moduleName);
        if ($module) {
            $version = $module['setup_version'];
        }
        return $version;
    }

    /**
     * Get the status of a module
     * @param $moduleName
     * @return string|null
     */
    protected function getStatus($moduleName): ?string
    {
        $status = 'Disabled';
        if ($this->moduleManager->isEnabled($moduleName)) {
            $status = 'Enabled';
        }

        return $status;
    }
}
