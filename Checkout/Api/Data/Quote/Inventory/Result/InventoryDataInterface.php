<?php

namespace Bold\Checkout\Api\Data\Quote\Inventory\Result;

/**
 * Quote item inventory data.
 */
interface InventoryDataInterface
{
    /**
     * Retrieve quote item id.
     *
     * @return int
     */
    public function getCartItemId(): int;

    /**
     * Retrieve quote item salable status.
     *
     * @return bool
     */
    public function isSalable(): bool;

    /**
     * Retrieve quote item salable quantity.
     *
     * @return float
     */
    public function getSalableQty(): float;
}
