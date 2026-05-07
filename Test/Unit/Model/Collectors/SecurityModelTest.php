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

namespace MagePulse\Collector\Test\Unit\Model\Collectors;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use MagePulse\Collector\Model\Collectors\SecurityModel;
use PHPUnit\Framework\TestCase;

class SecurityModelTest extends TestCase
{
    private ModuleManager $moduleManager;
    private ScopeConfigInterface $scopeConfig;
    private SecurityModel $model;

    protected function setUp(): void
    {
        $this->moduleManager = $this->createMock(ModuleManager::class);
        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->model = new SecurityModel($this->moduleManager, $this->scopeConfig);
    }

    public function testGetDataReturnsExpectedStructure(): void
    {
        $this->moduleManager->method('isEnabled')->willReturn(false);

        $data = $this->model->getData();

        $this->assertArrayHasKey('two_factor_auth', $data);
        $this->assertArrayHasKey('module_installed', $data['two_factor_auth']);
        $this->assertArrayHasKey('enabled', $data['two_factor_auth']);
    }

    public function testGetDataWhenTwoFactorAuthModuleNotInstalled(): void
    {
        $this->moduleManager->method('isEnabled')
            ->with('Magento_TwoFactorAuth')
            ->willReturn(false);

        $data = $this->model->getData();

        $this->assertFalse($data['two_factor_auth']['module_installed']);
        $this->assertNull($data['two_factor_auth']['enabled']);
    }

    public function testGetDataWhenTwoFactorAuthInstalledAndEnabled(): void
    {
        $this->moduleManager->method('isEnabled')
            ->with('Magento_TwoFactorAuth')
            ->willReturn(true);

        $this->scopeConfig->method('getValue')
            ->with('twofactorauth/general/enable')
            ->willReturn('1');

        $data = $this->model->getData();

        $this->assertTrue($data['two_factor_auth']['module_installed']);
        $this->assertTrue($data['two_factor_auth']['enabled']);
    }

    public function testGetDataWhenTwoFactorAuthInstalledButDisabled(): void
    {
        $this->moduleManager->method('isEnabled')
            ->with('Magento_TwoFactorAuth')
            ->willReturn(true);

        $this->scopeConfig->method('getValue')
            ->with('twofactorauth/general/enable')
            ->willReturn('0');

        $data = $this->model->getData();

        $this->assertTrue($data['two_factor_auth']['module_installed']);
        $this->assertFalse($data['two_factor_auth']['enabled']);
    }

    public function testScopeConfigIsNotQueriedWhenModuleNotInstalled(): void
    {
        $this->moduleManager->method('isEnabled')->willReturn(false);

        $this->scopeConfig->expects($this->never())->method('getValue');

        $this->model->getData();
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(CollectorInterface::class, $this->model);
    }
}
