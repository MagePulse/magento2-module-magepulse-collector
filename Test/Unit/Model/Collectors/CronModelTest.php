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

use Magento\Cron\Model\ResourceModel\Schedule\Collection;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Cron\Model\Schedule;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use MagePulse\Collector\Model\Collectors\CronModel;
use PHPUnit\Framework\TestCase;

class CronModelTest extends TestCase
{
    private CollectionFactory $collectionFactory;
    private CronModel $model;

    protected function setUp(): void
    {
        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->model = new CronModel($this->collectionFactory);
    }

    private function buildCollectionMock(?string $finishedAt, ?string $scheduledAt): Collection
    {
        $collection = $this->createMock(Collection::class);
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('setOrder')->willReturnSelf();
        $collection->method('setPageSize')->willReturnSelf();

        $item = $this->createMock(Schedule::class);
        $item->method('getId')->willReturn(null);
        $item->method('getFinishedAt')->willReturn($finishedAt);
        $item->method('getScheduledAt')->willReturn($scheduledAt);

        $collection->method('getFirstItem')->willReturn($item);

        return $collection;
    }

    public function testGetDataReturnsExpectedKeys(): void
    {
        $emptyCollection = $this->buildCollectionMock(null, null);
        $this->collectionFactory->method('create')->willReturn($emptyCollection);

        $data = $this->model->getData();

        $this->assertArrayHasKey('last_successful_run', $data);
        $this->assertArrayHasKey('last_error', $data);
        $this->assertArrayHasKey('running_recently', $data);
    }

    public function testGetDataReturnsNullsWhenNoCronJobsExist(): void
    {
        $emptyCollection = $this->buildCollectionMock(null, null);
        $this->collectionFactory->method('create')->willReturn($emptyCollection);

        $data = $this->model->getData();

        $this->assertNull($data['last_successful_run']);
        $this->assertNull($data['last_error']);
        $this->assertFalse($data['running_recently']);
    }

    public function testGetDataReportsRecentSuccessfulRun(): void
    {
        $recentTimestamp = date('Y-m-d H:i:s', time() - 300);

        $successCollection = $this->createMock(Collection::class);
        $successCollection->method('addFieldToFilter')->willReturnSelf();
        $successCollection->method('setOrder')->willReturnSelf();
        $successCollection->method('setPageSize')->willReturnSelf();

        $successItem = $this->createMock(Schedule::class);
        $successItem->method('getId')->willReturn('1');
        $successItem->method('getFinishedAt')->willReturn($recentTimestamp);
        $successCollection->method('getFirstItem')->willReturn($successItem);

        $errorCollection = $this->createMock(Collection::class);
        $errorCollection->method('addFieldToFilter')->willReturnSelf();
        $errorCollection->method('setOrder')->willReturnSelf();
        $errorCollection->method('setPageSize')->willReturnSelf();

        $emptyItem = $this->createMock(Schedule::class);
        $emptyItem->method('getId')->willReturn(null);
        $errorCollection->method('getFirstItem')->willReturn($emptyItem);

        $this->collectionFactory->method('create')->willReturnOnConsecutiveCalls(
            $successCollection,
            $errorCollection
        );

        $data = $this->model->getData();

        $this->assertSame($recentTimestamp, $data['last_successful_run']);
        $this->assertTrue($data['running_recently']);
        $this->assertNull($data['last_error']);
    }

    public function testGetDataFlagsAsNotRunningRecentlyForOldRun(): void
    {
        $oldTimestamp = date('Y-m-d H:i:s', time() - 7200);

        $successCollection = $this->createMock(Collection::class);
        $successCollection->method('addFieldToFilter')->willReturnSelf();
        $successCollection->method('setOrder')->willReturnSelf();
        $successCollection->method('setPageSize')->willReturnSelf();

        $successItem = $this->createMock(Schedule::class);
        $successItem->method('getId')->willReturn('1');
        $successItem->method('getFinishedAt')->willReturn($oldTimestamp);
        $successCollection->method('getFirstItem')->willReturn($successItem);

        $errorCollection = $this->createMock(Collection::class);
        $errorCollection->method('addFieldToFilter')->willReturnSelf();
        $errorCollection->method('setOrder')->willReturnSelf();
        $errorCollection->method('setPageSize')->willReturnSelf();

        $emptyItem = $this->createMock(Schedule::class);
        $emptyItem->method('getId')->willReturn(null);
        $errorCollection->method('getFirstItem')->willReturn($emptyItem);

        $this->collectionFactory->method('create')->willReturnOnConsecutiveCalls(
            $successCollection,
            $errorCollection
        );

        $data = $this->model->getData();

        $this->assertFalse($data['running_recently']);
    }

    public function testGetDataIncludesLastErrorTimestamp(): void
    {
        $errorTimestamp = date('Y-m-d H:i:s', time() - 600);

        $emptyCollection = $this->buildCollectionMock(null, null);

        $errorCollection = $this->createMock(Collection::class);
        $errorCollection->method('addFieldToFilter')->willReturnSelf();
        $errorCollection->method('setOrder')->willReturnSelf();
        $errorCollection->method('setPageSize')->willReturnSelf();

        $errorItem = $this->createMock(Schedule::class);
        $errorItem->method('getId')->willReturn('2');
        $errorItem->method('getScheduledAt')->willReturn($errorTimestamp);
        $errorCollection->method('getFirstItem')->willReturn($errorItem);

        $this->collectionFactory->method('create')->willReturnOnConsecutiveCalls(
            $emptyCollection,
            $errorCollection
        );

        $data = $this->model->getData();

        $this->assertSame($errorTimestamp, $data['last_error']);
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(CollectorInterface::class, $this->model);
    }
}
