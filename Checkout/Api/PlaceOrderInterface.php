<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface;

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
     * @return \Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface
     */
    public function place(string $shopId, OrderDataInterface $order): ResponseInterface;
}
