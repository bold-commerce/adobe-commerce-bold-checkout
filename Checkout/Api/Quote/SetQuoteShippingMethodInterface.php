<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;

/**
 * Set shipping method for quote.
 */
interface SetQuoteShippingMethodInterface
{
    /**
     * Set shipping method for quote.
     *
     * @param string $shopId
     * @param int $cartId
     * @param string $shippingMethodCode
     * @param string $shippingCarrierCode
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function setShippingMethod(
        string $shopId,
        int $cartId,
        string $shippingMethodCode,
        string $shippingCarrierCode
    ): ResultInterface;
}
