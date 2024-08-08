<?php

namespace Bold\Checkout\Api\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Gets the order from the quote id.
 */
interface GetOrderFromQuoteIdInterface
{
    /**
     * @param int $quoteId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder(int $quoteId): OrderInterface;
}
