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
use SodiumException;
use MagePulse\Collector\Model\Collector;
use MagePulse\Collector\Model\CollectorPool;
use MagePulse\Collector\Model\ConfigProvider;
use MagePulse\Collector\Model\Encryptor;

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
        /**
        $data = [
            'publicKey: ' => $this->configProvider->getPublicKey(),
            'privateKey: ' => $this->configProvider->getPrivateKey()
        ];
         */
        $data = $this->collectorPool->collect(CollectorPool::DEFAULT_SERVICE_GROUP);
        $result->setData(['error' => false, 'data' => $data, 'encryptedData' => $this->encryptor->encrypt(json_encode($data))]);
        $result->setHttpResponseCode(200);
        return $result;
    }
}
