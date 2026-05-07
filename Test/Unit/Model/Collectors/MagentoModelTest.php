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

use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\State;
use MagePulse\Collector\Model\Collectors\MagentoModel;
use PHPUnit\Framework\TestCase;

class MagentoModelTest extends TestCase
{
    private ProductMetadataInterface $metaData;
    private State $state;
    private MagentoModel $model;

    protected function setUp(): void
    {
        $this->metaData = $this->createMock(ProductMetadataInterface::class);
        $this->state = $this->createMock(State::class);

        $this->model = new MagentoModel($this->metaData, $this->state);
    }

    public function testGetDataReturnsAllExpectedKeys(): void
    {
        $this->metaData->method('getVersion')->willReturn('2.4.6');
        $this->metaData->method('getEdition')->willReturn('Community');
        $this->metaData->method('getName')->willReturn('Magento');
        $this->state->method('getMode')->willReturn('production');

        $data = $this->model->getData();

        $this->assertArrayHasKey('Magento Version', $data);
        $this->assertArrayHasKey('Magento Edition', $data);
        $this->assertArrayHasKey('Magento Name', $data);
        $this->assertArrayHasKey('Mode', $data);
        $this->assertArrayHasKey('PHP Version', $data);
    }

    public function testGetDataReturnsCorrectValues(): void
    {
        $this->metaData->method('getVersion')->willReturn('2.4.6');
        $this->metaData->method('getEdition')->willReturn('Community');
        $this->metaData->method('getName')->willReturn('Magento');
        $this->state->method('getMode')->willReturn('production');

        $data = $this->model->getData();

        $this->assertSame('2.4.6', $data['Magento Version']);
        $this->assertSame('Community', $data['Magento Edition']);
        $this->assertSame('Magento', $data['Magento Name']);
        $this->assertSame('production', $data['Mode']);
        $this->assertSame(phpversion(), $data['PHP Version']);
    }

    public function testGetDataReflectsEnterpriseEdition(): void
    {
        $this->metaData->method('getVersion')->willReturn('2.4.6');
        $this->metaData->method('getEdition')->willReturn('Enterprise');
        $this->metaData->method('getName')->willReturn('Magento');
        $this->state->method('getMode')->willReturn('production');

        $data = $this->model->getData();

        $this->assertSame('Enterprise', $data['Magento Edition']);
    }

    public function testGetDataReflectsDeveloperMode(): void
    {
        $this->metaData->method('getVersion')->willReturn('2.4.6');
        $this->metaData->method('getEdition')->willReturn('Community');
        $this->metaData->method('getName')->willReturn('Magento');
        $this->state->method('getMode')->willReturn('developer');

        $data = $this->model->getData();

        $this->assertSame('developer', $data['Mode']);
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(
            \MagePulse\Collector\Model\Collectors\CollectorInterface::class,
            $this->model
        );
    }
}
