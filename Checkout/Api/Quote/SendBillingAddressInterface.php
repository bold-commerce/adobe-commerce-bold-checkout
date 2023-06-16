<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Send billing address to Bold.
 */
interface SendBillingAddressInterface
{
    /**
     * Send billing address to Bold.
     *
     * @param string $shopId
     * @param AddressInterface $address
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send(string $shopId, AddressInterface $address): void;
}
