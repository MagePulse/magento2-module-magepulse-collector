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

use Magento\Indexer\Model\Indexer;
use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;
use MagePulse\Collector\Model\Collectors\CollectorInterface;
use MagePulse\Collector\Model\Collectors\IndexerModel;
use PHPUnit\Framework\TestCase;

class IndexerModelTest extends TestCase
{
    private IndexerCollection $indexerCollection;
    private IndexerModel $model;

    protected function setUp(): void
    {
        $this->indexerCollection = $this->createMock(IndexerCollection::class);
        $this->model = new IndexerModel($this->indexerCollection);
    }

    public function testGetDataReturnsEmptyArrayWhenNoIndexers(): void
    {
        $this->indexerCollection->method('getItems')->willReturn([]);

        $data = $this->model->getData();

        $this->assertIsArray($data);
        $this->assertEmpty($data);
    }

    public function testGetDataReturnsIndexerWithExpectedKeys(): void
    {
        $indexer = $this->createMock(Indexer::class);
        $indexer->method('getId')->willReturn('catalog_product_flat');
        $indexer->method('getTitle')->willReturn('Product Flat Data');
        $indexer->method('getStatus')->willReturn('valid');

        $this->indexerCollection->method('getItems')->willReturn([$indexer]);

        $data = $this->model->getData();

        $this->assertCount(1, $data);
        $this->assertArrayHasKey('id', $data[0]);
        $this->assertArrayHasKey('title', $data[0]);
        $this->assertArrayHasKey('status', $data[0]);
        $this->assertArrayHasKey('needs_attention', $data[0]);
    }

    public function testGetDataReturnsCorrectIndexerValues(): void
    {
        $indexer = $this->createMock(Indexer::class);
        $indexer->method('getId')->willReturn('catalog_product_flat');
        $indexer->method('getTitle')->willReturn('Product Flat Data');
        $indexer->method('getStatus')->willReturn('valid');

        $this->indexerCollection->method('getItems')->willReturn([$indexer]);

        $data = $this->model->getData();

        $this->assertSame('catalog_product_flat', $data[0]['id']);
        $this->assertSame('Product Flat Data', $data[0]['title']);
        $this->assertSame('valid', $data[0]['status']);
        $this->assertFalse($data[0]['needs_attention']);
    }

    public function testGetDataFlagsInvalidStatusAsNeedsAttention(): void
    {
        $indexer = $this->createMock(Indexer::class);
        $indexer->method('getId')->willReturn('catalogsearch_fulltext');
        $indexer->method('getTitle')->willReturn('Catalog Search');
        $indexer->method('getStatus')->willReturn('invalid');

        $this->indexerCollection->method('getItems')->willReturn([$indexer]);

        $data = $this->model->getData();

        $this->assertTrue($data[0]['needs_attention']);
    }

    public function testGetDataFlagsWorkingStatusAsNeedsAttention(): void
    {
        $indexer = $this->createMock(Indexer::class);
        $indexer->method('getId')->willReturn('catalog_category_product');
        $indexer->method('getTitle')->willReturn('Category Products');
        $indexer->method('getStatus')->willReturn('working');

        $this->indexerCollection->method('getItems')->willReturn([$indexer]);

        $data = $this->model->getData();

        $this->assertTrue($data[0]['needs_attention']);
    }

    public function testGetDataHandlesMultipleIndexers(): void
    {
        $indexer1 = $this->createMock(Indexer::class);
        $indexer1->method('getId')->willReturn('catalog_product_flat');
        $indexer1->method('getTitle')->willReturn('Product Flat Data');
        $indexer1->method('getStatus')->willReturn('valid');

        $indexer2 = $this->createMock(Indexer::class);
        $indexer2->method('getId')->willReturn('catalog_product_price');
        $indexer2->method('getTitle')->willReturn('Product Price');
        $indexer2->method('getStatus')->willReturn('invalid');

        $this->indexerCollection->method('getItems')->willReturn([$indexer1, $indexer2]);

        $data = $this->model->getData();

        $this->assertCount(2, $data);
        $this->assertFalse($data[0]['needs_attention']);
        $this->assertTrue($data[1]['needs_attention']);
    }

    public function testImplementsCollectorInterface(): void
    {
        $this->assertInstanceOf(CollectorInterface::class, $this->model);
    }
}
