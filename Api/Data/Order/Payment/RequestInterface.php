<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Update payment request data interface.
 *
 * Represents a request data to update order payment
 * used in the /V1/shops/:shopId/payments endpoint. @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\Order\UpdatePaymentInterface::update()
 * @api
 */
interface RequestInterface
{
    /**
     * Add payment object to the request.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
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
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
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
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(RequestExtensionInterface $extensionAttributes): void;

    /**
     * Get extension attributes from the request. Used in case additional fields are sent in the request.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface;
}
