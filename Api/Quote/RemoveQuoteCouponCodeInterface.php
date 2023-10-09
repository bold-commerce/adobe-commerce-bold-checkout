<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;

/**
 * Remove coupon code from quote.
 */
interface RemoveQuoteCouponCodeInterface
{
    /**
     * Remove coupon code from quote.
     *
     * @param string $shopId
     * @param int $cartId
     * @param string|null $couponCode
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function removeCoupon(string $shopId, int $cartId, string $couponCode = null): ResultInterface;
}
