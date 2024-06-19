<?php

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Update payment request interface.
 */
interface RequestInterface
{
    /**
     * Add payment object to the request.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function setPayment(OrderPaymentInterface $payment): void;

    /**
     * Get payment object from the request.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|null
     */
    public function getPayment(): ?OrderPaymentInterface;

    /**
     * Set transaction object to the request.
     *
     * @param \Magento\Sales\Api\Data\TransactionInterface $transaction
     * @return void
     */
    public function setTransaction(TransactionInterface $transaction): void;

    /**
     * Get transaction object from the request.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface|null
     */
    public function getTransaction(): ?TransactionInterface;

    /**
     * Set extension attributes to the request.
     *
     * @param \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(RequestExtensionInterface $extensionAttributes): void;

    /**
     * Get extension attributes from the request.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface;
}
