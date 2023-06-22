<?php

namespace Bold\Checkout\Api\Order;

use Bold\Checkout\Api\Data\Order\Payment\RequestInterface;
use Bold\Checkout\Api\Data\Order\Payment\ResultInterface;

/**
 * Update order payment and create invoice service.
 */
interface UpdatePaymentInterface
{
    /**
     * Update order payment and create invoice.
     *
     * @param string $shopId
     * @param \Bold\Checkout\Api\Data\Order\Payment\RequestInterface $payment
     * @return \Bold\Checkout\Api\Data\Order\Payment\ResultInterface
     */
    public function update(string $shopId, RequestInterface $payment): ResultInterface;
}
