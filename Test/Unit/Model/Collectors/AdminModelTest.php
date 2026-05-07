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

use Magento\Framework\App\DeploymentConfig;
use MagePulse\Collector\Model\Collectors\AdminModel;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use PHPUnit\Framework\TestCase;

class AdminModelTest extends TestCase
{
    private DeploymentConfig $deploymentConfig;
    private AdminModel $model;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->model = new AdminModel($this->deploymentConfig);
    }

    public function testGetDataReturnsAdminPathKey(): void
    {
        $this->deploymentConfig->method('get')->willReturn('admin');

        $data = $this->model->getData();

        $this->assertArrayHasKey('admin_path', $data);
    }

    public function testGetDataReturnsCustomAdminPath(): void
    {
        $this->deploymentConfig->method('get')
            ->with('backend/frontName', 'admin')
            ->willReturn('secret-panel');

        $data = $this->model->getData();

        $this->assertSame('secret-panel', $data['admin_path']);
    }

    public function testGetDataReturnsDefaultAdminPath(): void
    {
        $this->deploymentConfig->method('get')
            ->with('backend/frontName', 'admin')
            ->willReturn('admin');

        $data = $this->model->getData();

        $this->assertSame('admin', $data['admin_path']);
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(CollectorInterface::class, $this->model);
    }
}
