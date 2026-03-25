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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class SecurityModel implements CollectorInterface
{
    private ModuleManager $moduleManager;
    private ScopeConfigInterface $scopeConfig;

    public function __construct(
        ModuleManager $moduleManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->moduleManager = $moduleManager;
        $this->scopeConfig = $scopeConfig;
    }

    public function getData(): array
    {
        $moduleInstalled = $this->moduleManager->isEnabled('Magento_TwoFactorAuth');

        return [
            'two_factor_auth' => [
                'module_installed' => $moduleInstalled,
                'enabled' => $moduleInstalled
                    ? (bool) $this->scopeConfig->getValue('twofactorauth/general/enable')
                    : null,
            ],
        ];
    }
}
