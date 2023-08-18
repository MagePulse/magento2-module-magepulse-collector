<?php

declare(strict_types=1);

namespace MagePulse\Collector\Model\Collectors;

use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Module\ModuleListInterface;
use MagePulse\Collector\Model\ModuleMetaInfo;

class PluginModel implements CollectorInterface
{
    private ModuleListInterface $moduleList;
    private FullModuleList $fullModuleList;
    private ModuleManager $moduleManager;
    private ModuleMetaInfo $moduleMetaInfo;

    public function __construct(
        FullModuleList $fullModuleList,
        ModuleListInterface $moduleList,
        ModuleManager $moduleManager,
        ModuleMetaInfo $moduleMetaInfo
    ) {
        $this->fullModuleList = $fullModuleList;
        $this->moduleList = $moduleList;
        $this->moduleManager = $moduleManager;
        $this->moduleMetaInfo = $moduleMetaInfo;
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
            $modules[] = [
                'name' => $module['name'],
                'composer_version' => $this->moduleMetaInfo->getModuleMeta($module['name'])['version'] ?? '0.0.0',
                'module_version' => $this->getVersion($module['name']),
                'enabled' => $this->getStatus($module['name']),
                'license' => $this->moduleMetaInfo->getModuleMeta($module['name'])['license'] ?? 'N/A',
//                'raw' => $this->moduleMetaInfo->getModuleMeta($module['name']),
                'support' => $this->moduleMetaInfo->getModuleMeta($module['name'])['support'] ?? 'N/A',
            ];
        }

        return $modules;
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
