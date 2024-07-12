<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Mark order as completed.
 */
interface CompleteOrderInterface
{
    /**
     * Mark order as completed.
     *
     * @param OrderInterface $order
     * @return void
     */
    public function execute(OrderInterface $order): void;
}
