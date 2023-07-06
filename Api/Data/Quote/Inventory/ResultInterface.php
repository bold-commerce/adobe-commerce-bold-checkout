<?php

namespace Bold\Checkout\Api\Data\Quote\Inventory;

/**
 * Quote inventory result.
 */
interface ResultInterface
{
    /**
     * Retrieve inventory data.
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
}
