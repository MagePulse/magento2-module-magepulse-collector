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

namespace MagePulse\Collector\Test\Unit\Model;

use Magento\Framework\Exception\NotFoundException;
use MagePulse\Collector\Model\Collector;
use MagePulse\Collector\Model\CollectorPool;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use PHPUnit\Framework\TestCase;

class CollectorTest extends TestCase
{
    private CollectorPool $collectorPool;
    private Collector $collector;

    protected function setUp(): void
    {
        $this->collectorPool = $this->createMock(CollectorPool::class);
        $this->collector = new Collector($this->collectorPool);
    }

    public function testCollectAggregatesDataFromAllCollectorsInGroup(): void
    {
        $collectorA = $this->createMock(CollectorInterface::class);
        $collectorA->method('getData')->willReturn(['key' => 'valueA']);

        $collectorB = $this->createMock(CollectorInterface::class);
        $collectorB->method('getData')->willReturn(['key' => 'valueB']);

        $this->collectorPool->method('retrieve')
            ->with('mainGroup')
            ->willReturn(['collectorA' => $collectorA, 'collectorB' => $collectorB]);

        $result = $this->collector->collect('mainGroup');

        $this->assertSame(['key' => 'valueA'], $result['collectorA']);
        $this->assertSame(['key' => 'valueB'], $result['collectorB']);
    }

    public function testCollectReturnsEmptyArrayForGroupWithNoCollectors(): void
    {
        $this->collectorPool->method('retrieve')
            ->with('emptyGroup')
            ->willReturn([]);

        $result = $this->collector->collect('emptyGroup');

        $this->assertSame([], $result);
    }

    public function testCollectUsesCollectorNameAsKey(): void
    {
        $collectorA = $this->createMock(CollectorInterface::class);
        $collectorA->method('getData')->willReturn(['foo' => 'bar']);

        $this->collectorPool->method('retrieve')
            ->willReturn(['myCollector' => $collectorA]);

        $result = $this->collector->collect('mainGroup');

        $this->assertArrayHasKey('myCollector', $result);
    }

    public function testCollectPropagatesNotFoundExceptionFromPool(): void
    {
        $this->collectorPool->method('retrieve')
            ->willThrowException(new NotFoundException(__('Collector pool missing does not exist')));

        $this->expectException(NotFoundException::class);

        $this->collector->collect('missing');
    }

    public function testCollectCallsGetDataOnEachCollector(): void
    {
        $collectorA = $this->createMock(CollectorInterface::class);
        $collectorA->expects($this->once())->method('getData')->willReturn([]);

        $collectorB = $this->createMock(CollectorInterface::class);
        $collectorB->expects($this->once())->method('getData')->willReturn([]);

        $this->collectorPool->method('retrieve')
            ->willReturn(['a' => $collectorA, 'b' => $collectorB]);

        $this->collector->collect('mainGroup');
    }
}
