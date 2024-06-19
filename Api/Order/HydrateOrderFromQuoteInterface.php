<?php

namespace Bold\Checkout\Api\Order;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Hydrate Bold order from Magento quote.
 */
interface HydrateOrderFromQuoteInterface
{
    /**
     * Hydrate Bold simple order with Magento quote data
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $publicOrderId
     * @return void
     */
    public function hydrate(CartInterface $quote, string $publicOrderId): void;
}
