<?php

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
