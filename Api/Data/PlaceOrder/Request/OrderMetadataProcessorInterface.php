<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder\Request;

use Magento\Sales\Api\Data\OrderInterface;

/**
 * Process order data extension attributes from request.
 */
interface OrderMetadataProcessorInterface
{
    /**
     * Process order data extension attributes from request.
     *
     * @param \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface $orderDataExtension
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return void
     */
    public function process(OrderDataExtensionInterface $orderDataExtension, OrderInterface $order): void;
}
