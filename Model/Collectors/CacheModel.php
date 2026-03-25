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

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Config as PageCacheConfig;

class CacheModel implements CollectorInterface
{
    private PageCacheConfig $pageCacheConfig;
    private TypeListInterface $typeList;

    public function __construct(
        PageCacheConfig $pageCacheConfig,
        TypeListInterface $typeList
    ) {
        $this->pageCacheConfig = $pageCacheConfig;
        $this->typeList = $typeList;
    }

    public function getData(): array
    {
        return [
            'full_page_cache' => [
                'enabled' => $this->pageCacheConfig->isEnabled(),
                'type'    => $this->pageCacheConfig->getType() === PageCacheConfig::VARNISH
                    ? 'Varnish' : 'Built-in',
            ],
            'cache_types' => $this->getCacheTypes(),
        ];
    }

    private function getCacheTypes(): array
    {
        $types = [];
        foreach ($this->typeList->getTypes() as $type) {
            $types[] = [
                'id'     => $type->getId(),
                'label'  => $type->getCacheType(),
                'status' => $type->getStatus() ? 'enabled' : 'disabled',
            ];
        }
        return $types;
    }
}
