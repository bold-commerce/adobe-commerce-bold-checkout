<?php

declare(strict_types=1);

namespace Bold\Checkout\Api;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterface;

/**
 * Push bold order to m2 platform.
 *
 * @api
 */
interface PlaceOrderInterface
{
    /**
     * Place order from request.
     *
     * @param string $shopId
     * @param \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface $order
     * @return \Bold\Checkout\Api\Data\PlaceOrder\ResultInterface
     */
    public function place(string $shopId, OrderDataInterface $order): ResultInterface;

    /**
     * @param string $shopId
     * @param string $quoteMaskId
     * @return ResultInterface
     */
    public function authorizeAndPlace(string $publicOrderId, string $quoteMaskId): ResultInterface;
}
