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

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Config as PageCacheConfig;
use MagePulse\Collector\Model\Collectors\CacheModel;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use PHPUnit\Framework\TestCase;

class CacheModelTest extends TestCase
{
    private PageCacheConfig $pageCacheConfig;
    private TypeListInterface $typeList;
    private CacheModel $model;

    protected function setUp(): void
    {
        $this->pageCacheConfig = $this->createMock(PageCacheConfig::class);
        $this->typeList = $this->createMock(TypeListInterface::class);
        $this->model = new CacheModel($this->pageCacheConfig, $this->typeList);
    }

    public function testGetDataReturnsExpectedStructure(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);
        $this->typeList->method('getTypes')->willReturn([]);

        $data = $this->model->getData();

        $this->assertArrayHasKey('full_page_cache', $data);
        $this->assertArrayHasKey('cache_types', $data);
        $this->assertArrayHasKey('enabled', $data['full_page_cache']);
        $this->assertArrayHasKey('type', $data['full_page_cache']);
    }

    public function testGetDataReflectsBuiltInCacheType(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);
        $this->typeList->method('getTypes')->willReturn([]);

        $data = $this->model->getData();

        $this->assertTrue($data['full_page_cache']['enabled']);
        $this->assertSame('Built-in', $data['full_page_cache']['type']);
    }

    public function testGetDataReflectsVarnishCacheType(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::VARNISH);
        $this->typeList->method('getTypes')->willReturn([]);

        $data = $this->model->getData();

        $this->assertSame('Varnish', $data['full_page_cache']['type']);
    }

    public function testGetDataReflectsDisabledFullPageCache(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(false);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);
        $this->typeList->method('getTypes')->willReturn([]);

        $data = $this->model->getData();

        $this->assertFalse($data['full_page_cache']['enabled']);
    }

    public function testGetDataIncludesCacheTypes(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);

        $cacheType = new class {
            public function getId(): string
            {
                return 'config';
            }
            public function getCacheType(): string
            {
                return 'Configuration';
            }
            public function getStatus(): int
            {
                return 1;
            }
        };
        $this->typeList->method('getTypes')->willReturn([$cacheType]);

        $data = $this->model->getData();

        $this->assertCount(1, $data['cache_types']);
        $this->assertSame('config', $data['cache_types'][0]['id']);
        $this->assertSame('Configuration', $data['cache_types'][0]['label']);
        $this->assertSame('enabled', $data['cache_types'][0]['status']);
    }

    public function testGetDataShowsDisabledCacheTypeStatus(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);

        $cacheType = new class {
            public function getId(): string
            {
                return 'block_html';
            }
            public function getCacheType(): string
            {
                return 'Blocks HTML output';
            }
            public function getStatus(): int
            {
                return 0;
            }
        };
        $this->typeList->method('getTypes')->willReturn([$cacheType]);

        $data = $this->model->getData();

        $this->assertSame('disabled', $data['cache_types'][0]['status']);
    }

    public function testGetDataReturnsEmptyCacheTypesWhenNoneRegistered(): void
    {
        $this->pageCacheConfig->method('isEnabled')->willReturn(true);
        $this->pageCacheConfig->method('getType')->willReturn(PageCacheConfig::BUILT_IN);
        $this->typeList->method('getTypes')->willReturn([]);

        $data = $this->model->getData();

        $this->assertIsArray($data['cache_types']);
        $this->assertEmpty($data['cache_types']);
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(CollectorInterface::class, $this->model);
    }
}
