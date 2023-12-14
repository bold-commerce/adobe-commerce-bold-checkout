<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\Inventory\Result;

/**
 * Quote item inventory data. Used in the response
 * of the /V1/shops/:shopId/cart/:cartId/inventory endpoint. @see Bold/Checkout/etc/webapi.xml
 *
 * @see \Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface::getInventoryData()
 * @api
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
     * Retrieve inventory result extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in quote item inventory result object, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?InventoryDataExtensionInterface;
}
