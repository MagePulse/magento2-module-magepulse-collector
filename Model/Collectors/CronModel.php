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

use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Cron\Model\Schedule;

class CronModel implements CollectorInterface
{
    private const HEALTHY_WINDOW_SECONDS = 1800;

    private CollectionFactory $collectionFactory;

    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    public function getData(): array
    {
        $lastSuccess = $this->getLastByStatus(Schedule::STATUS_SUCCESS);
        $lastError   = $this->getLastByStatus(Schedule::STATUS_ERROR);

        $healthyThreshold = date('Y-m-d H:i:s', time() - self::HEALTHY_WINDOW_SECONDS);

        return [
            'last_successful_run' => $lastSuccess ? $lastSuccess->getFinishedAt() : null,
            'last_error'          => $lastError   ? $lastError->getScheduledAt()  : null,
            'running_recently'    => $lastSuccess !== null
                && $lastSuccess->getFinishedAt() >= $healthyThreshold,
        ];
    }

    private function getLastByStatus(string $status): ?Schedule
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', $status)
                   ->setOrder('finished_at', 'DESC')
                   ->setPageSize(1);
        /** @var Schedule $item */
        $item = $collection->getFirstItem();
        return $item->getId() ? $item : null;
    }
}
