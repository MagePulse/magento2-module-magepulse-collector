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

namespace MagePulse\Collector\Model;

use Magento\Framework\Exception\NotFoundException;
use MagePulse\Collector\Model\Collectors\CollectorInterface;

class CollectorPool
{
    public const DEFAULT_SERVICE_GROUP = 'mainGroup';

    /**
     * @var CollectorInterface[]
     */
    private array $collectors;

    public function __construct(array $collectors)
    {
        $this->checkCollectors($collectors);
        $this->collectors = $collectors;
    }

    /**
     * Check the collectors implement the CollectorInterface
     *
     * @param $collectors
     * @return void
     */
    private function checkCollectors($collectors): void
    {
        foreach ($collectors as $groupName => $groupCollectors) {
            foreach ($groupCollectors as $collectorName => $collector) {
                if (!$collector instanceof CollectorInterface) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Collector %s doesn\'t implement %s',
                            get_class($collector),
                            CollectorInterface::class
                        )
                    );
                }
            }
        }
    }

    /**
     * Return the pool data
     *
     * @param string $groupName
     * @return array
     * @throws NotFoundException
     */
    public function retrieve(string $groupName): array
    {
        if (!isset($this->collectors[$groupName])) {
            throw new NotFoundException(
                __(
                    'Collector pool %s doesn\'t exist',
                    $groupName
                )
            );
        }

        return $this->collectors[$groupName];
    }
}
