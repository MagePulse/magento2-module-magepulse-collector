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

use Magento\Indexer\Model\Indexer\Collection as IndexerCollection;

class IndexerModel implements CollectorInterface
{
    private IndexerCollection $indexerCollection;

    public function __construct(IndexerCollection $indexerCollection)
    {
        $this->indexerCollection = $indexerCollection;
    }

    public function getData(): array
    {
        $indexers = [];
        foreach ($this->indexerCollection->getItems() as $indexer) {
            $status = $indexer->getStatus();
            $indexers[] = [
                'id'              => $indexer->getId(),
                'title'           => $indexer->getTitle(),
                'status'          => $status,
                'needs_attention' => in_array($status, ['invalid', 'working'], true),
            ];
        }
        return $indexers;
    }
}
