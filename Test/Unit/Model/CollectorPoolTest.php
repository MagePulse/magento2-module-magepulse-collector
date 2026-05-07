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
use MagePulse\Collector\Model\CollectorPool;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use PHPUnit\Framework\TestCase;

class CollectorPoolTest extends TestCase
{
    public function testRetrieveReturnsCollectorsForExistingGroup(): void
    {
        $collector = $this->createMock(CollectorInterface::class);
        $pool = new CollectorPool(['mainGroup' => [$collector]]);

        $this->assertSame([$collector], $pool->retrieve('mainGroup'));
    }

    public function testRetrieveThrowsNotFoundExceptionForMissingGroup(): void
    {
        $pool = new CollectorPool([]);

        $this->expectException(NotFoundException::class);
        $pool->retrieve('nonExistentGroup');
    }

    public function testConstructorThrowsInvalidArgumentExceptionForNonCollector(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/doesn\'t implement/');

        new CollectorPool(['mainGroup' => [new \stdClass()]]);
    }

    public function testConstructorAcceptsMultipleGroups(): void
    {
        $collector1 = $this->createMock(CollectorInterface::class);
        $collector2 = $this->createMock(CollectorInterface::class);

        $pool = new CollectorPool([
            'groupA' => [$collector1],
            'groupB' => [$collector2],
        ]);

        $this->assertSame([$collector1], $pool->retrieve('groupA'));
        $this->assertSame([$collector2], $pool->retrieve('groupB'));
    }

    public function testConstructorAcceptsMultipleCollectorsInGroup(): void
    {
        $collector1 = $this->createMock(CollectorInterface::class);
        $collector2 = $this->createMock(CollectorInterface::class);

        $pool = new CollectorPool(['mainGroup' => [$collector1, $collector2]]);

        $this->assertCount(2, $pool->retrieve('mainGroup'));
    }

    public function testConstructorAcceptsEmptyCollectors(): void
    {
        $pool = new CollectorPool([]);

        $this->expectException(NotFoundException::class);
        $pool->retrieve(CollectorPool::DEFAULT_SERVICE_GROUP);
    }

    public function testErrorMessageContainsInvalidClassName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/stdClass/');

        new CollectorPool(['mainGroup' => [new \stdClass()]]);
    }
}
