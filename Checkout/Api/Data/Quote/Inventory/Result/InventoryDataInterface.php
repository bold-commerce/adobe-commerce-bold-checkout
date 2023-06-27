<?php

namespace Bold\Checkout\Api\Data\Quote\Inventory\Result;

use \Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataExtensionInterface;

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
     * Set inventory result extension attributes.
     *
     * @param \Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(InventoryDataExtensionInterface $extensionAttributes): void;

    /**
     * Retrieve inventory result extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?InventoryDataExtensionInterface;
}
