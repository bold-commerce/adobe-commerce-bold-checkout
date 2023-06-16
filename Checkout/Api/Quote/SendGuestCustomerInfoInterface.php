<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;

/**
 * Send guest customer information to Bold.
 */
interface SendGuestCustomerInfoInterface
{
    /**
     * Send guest customer information to Bold.
     *
     * @param string $shopId
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @return \Bold\Checkout\Api\Data\Http\Client\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send(string $shopId, string $email, string $firstName, string $lastName): ResultInterface;
}
