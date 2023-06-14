<?php
namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface;

/**
 * Get quote items inventory data.
 */
interface GetQuoteInventoryDataInterface
{
    /**
     * Get quote items inventory data.
     *
     * @param string $shopId
     * @param int $cartId
     * @return \Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface
     */
    public function getInventory(string $shopId, int $cartId): ResultInterface;
}
