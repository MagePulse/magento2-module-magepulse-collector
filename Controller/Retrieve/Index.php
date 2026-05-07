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

namespace MagePulse\Collector\Controller\Retrieve;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use MagePulse\Collector\Model\Collector;
use MagePulse\Collector\Model\CollectorPool;
use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Model\Encryptor;
use SodiumException;

class Index implements HttpGetActionInterface
{
    protected ResultFactory $resultFactory;
    protected ConfigProvider $configProvider;
    private Collector $collectorPool;
    private Encryptor $encryptor;

    public function __construct(
        ResultFactory $resultFactory,
        ConfigProvider $configProvider,
        Collector $collectorPool,
        Encryptor $encryptor
    ) {
        $this->resultFactory = $resultFactory;
        $this->configProvider = $configProvider;
        $this->collectorPool = $collectorPool;
        $this->encryptor = $encryptor;
    }

    /**
     * Execute action based on request and return result
     * @throws NotFoundException
     * @throws SodiumException
     */
    public function execute()
    {
        // Check if the module is enabled
        if ($this->configProvider->isEnabled() === false) {
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('noroute');
            return $resultForward;
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        if (!$this->configProvider->isConfigured()) {
            $result->setData(['error' => true, 'message' => 'Collector configuration is incomplete. Please check your MagePulse settings.']);
            $result->setHttpResponseCode(500);
            return $result;
        }

        try {
            $time_start = microtime(true);
            $data = $this->collectorPool->collect(CollectorPool::DEFAULT_SERVICE_GROUP);
            $time_end = microtime(true);
            $result->setData(['error' => false, 'encryptedData' => $this->encryptor->encrypt(json_encode($data, JSON_THROW_ON_ERROR)), 'executionTime' => $time_end - $time_start]);
            $result->setHttpResponseCode(200);
        } catch (\JsonException $e) {
            $result->setData(['error' => true, 'message' => 'Failed to serialise collector data.']);
            $result->setHttpResponseCode(500);
        } catch (SodiumException $e) {
            $result->setData(['error' => true, 'message' => $e->getMessage()]);
            $result->setHttpResponseCode(500);
        }

        return $result;
    }
}
