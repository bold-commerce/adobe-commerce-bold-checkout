<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;

/**
 * Set coupon code for quote.
 */
interface SetQuoteCouponCodeInterface
{
    /**
     * Set coupon code for quote.
     *
     * @param string $shopId
     * @param int $cartId
     * @param string $couponCode
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface;
}
