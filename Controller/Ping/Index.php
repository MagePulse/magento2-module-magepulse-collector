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

namespace MagePulse\Collector\Controller\Ping;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;

/**
 * Health-check endpoint at /magepulse_collector/ping/index
 */
class Index extends Action
{
    private RawFactory $resultRawFactory;

    public function __construct(Context $context, RawFactory $resultRawFactory)
    {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
    }

    public function execute()
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-Type', 'text/plain; charset=UTF-8', true);
        $response->setHeader('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate', true);
        $response->setHttpResponseCode(200);
        $response->setContents('OK');

        return $response;
    }
}

