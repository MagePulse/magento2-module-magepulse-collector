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

namespace MagePulse\Collector\Service;

class Key
{
    private string $keyPair;

    /**
     * @throws \SodiumException
     */
    public function createKeyPair(): string
    {
        return $this->keyPair = sodium_crypto_box_keypair();
    }

    /**
     * @throws \SodiumException
     */
    public function getPublicKey(): string
    {
        return sodium_bin2hex(sodium_crypto_box_publickey($this->keyPair));
    }

    /**
     * @throws \SodiumException
     */
    public function getPrivateKey(): string
    {
        return sodium_bin2hex(sodium_crypto_box_secretkey($this->keyPair));
    }
}
