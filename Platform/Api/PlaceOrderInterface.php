<?php
declare(strict_types=1);

namespace Bold\Platform\Api;

use Bold\Platform\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Platform\Api\Data\PlaceOrder\ResponseInterface;

/**
 * Place order service.
 *
 * @api
 */
interface PlaceOrderInterface
{
    /**
     * Place order from request.
     *
     * @param string $shopId
     * @param \Bold\Platform\Api\Data\PlaceOrder\Request\OrderDataInterface $order
     * @return \Bold\Platform\Api\Data\PlaceOrder\ResponseInterface
     */
    public function place(string $shopId, OrderDataInterface $order): ResponseInterface;
}
