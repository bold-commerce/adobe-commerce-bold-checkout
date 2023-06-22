<?php

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Update payment result interface.
 */
interface ResultInterface
{
    /**
     * Set payment object to the response.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|null
     */
    public function getPayment(): ?OrderPaymentInterface;

    /**
     * Retrieve errors from the response.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Get extension attributes from the response.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
