<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder\Request;

use Magento\Quote\Api\Data\AddressInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Request order data interface.
 */
interface OrderDataInterface
{
    /**
     * Retrieve cart id.
     *
     * @return int
     */
    public function getQuoteId(): int;

    /**
     * Retrieve customer browser ip.
     *
     * @return string
     */
    public function getBrowserIp(): string;

    /**
     * Retrieve order bold public id.
     *
     * @return string
     */
    public function getPublicId(): string;

    /**
     * Retrieve order bold financial status.
     *
     * @return string
     */
    public function getFinancialStatus(): string;

    /**
     * Retrieve order bold fulfillment status.
     *
     * @return string
     */
    public function getFulfillmentStatus(): string;

    /**
     * Retrieve order status.
     *
     * @return string
     */
    public function getOrderStatus(): string;

    /**
     * Retrieve order number.
     *
     * @return string
     */
    public function getOrderNumber(): string;

    /**
     * Retrieve bold order total.
     *
     * @return float
     */
    public function getTotal(): float;

    /**
     * Retrieve request billing address.
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getBillingAddress(): AddressInterface;

    /**
     * Retrieve order payment request data.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function getPayment(): OrderPaymentInterface;

    /**
     * Retrieve payment transaction request data.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface
     */
    public function getTransaction(): TransactionInterface;

    /**
     * Retrieve order request extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OrderDataExtensionInterface;
}
