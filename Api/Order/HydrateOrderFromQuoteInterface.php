<?php

namespace Bold\Checkout\Api\Order;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Hydrate Bold order from Magento quote.
 */
interface HydrateOrderFromQuoteInterface
{
    /**
     * Hydrate Bold simple order with Magento quote data
     *
     * @param CartInterface $quote
     * @param string $publicOrderId
     * @return ResultInterface
     */
    public function hydrate(CartInterface $quote, string $publicOrderId): ResultInterface;
}
