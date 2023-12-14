<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\Inventory;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Quote inventory result. Used in the response
 * of the /V1/shops/:shopId/cart/:cartId/inventory endpoint. @see Bold/Checkout/etc/webapi.xml
 *
 * @see \Bold\Checkout\Api\Quote\GetQuoteInventoryDataInterface::getInventory()
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve inventory data for quote items.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataInterface[]
     */
    public function getInventoryData(): array;

    /**
     * Retrieve errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve response extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in quote inventory result object, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Inventory\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
