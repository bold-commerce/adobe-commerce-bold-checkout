<?php

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;

/**
 * Remove coupon code for quote.
 */
interface RemoveQuoteCouponCodeInterface
{
    /**
     * Remove coupon code for quote.
     *
     * @param string $shopId
     * @param int $cartId
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function removeCoupon(string $shopId, int $cartId): ResultInterface;
}
