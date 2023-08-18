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

namespace MagePulse\Collector\Model\Collectors;

use Magento\Framework\App\ProductMetadataInterface;

class MagentoModel implements CollectorInterface
{
    private ProductMetadataInterface $metaData;

    public function __construct(ProductMetadataInterface $metadata)
    {
        $this->metaData = $metadata;
    }

    public function getData(): array
    {
        return [
            'Magento Version' => $this->metaData->getVersion(),
            'Magento Edition' => $this->metaData->getEdition(),
            'Magento Name' => $this->metaData->getName(),
            'PHP Version' => phpversion()
        ];
    }
}
