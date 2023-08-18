<?php

declare(strict_types=1);

namespace MagePulse\Collector\Model;

use Magento\Framework\Exception\NotFoundException;

class Collector
{
    private CollectorPool $collectorPool;

    public function __construct(CollectorPool $collectorPool)
    {
        $this->collectorPool = $collectorPool;
    }

    /**
     * Get the collector group data
     *
     * @param string $groupName
     * @return array
     * @throws NotFoundException
     */
    public function collect(string $groupName): array
    {
        $data = [];
        $collectors = $this->collectorPool->retrieve($groupName);
        foreach ($collectors as $collectorName => $collector) {
            $data[$collectorName] = $collector->getData();
        }

        return $data;
    }
}
