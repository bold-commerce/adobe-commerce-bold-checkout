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
     * @param int $quoteId
     * @return void
     */
    public function setQuoteId(int $quoteId): void;

    /**
     * Retrieve customer browser ip.
     *
     * @return string
     */
    public function getBrowserIp(): string;

    /**
     * @param string $browserIp
     * @return void
     */
    public function setBrowserIp(string $browserIp): void;

    /**
     * Retrieve order bold public id.
     *
     * @return string
     */
    public function getPublicId(): string;

    /**
     * @param string $publicId
     * @return void
     */
    public function setPublicId(string $publicId): void;

    /**
     * Retrieve order bold financial status.
     *
     * @return string
     */
    public function getFinancialStatus(): string;

    /**
     * @param string $financialStatus
     * @return void
     */
    public function setFinancialStatus(string $financialStatus): void;

    /**
     * Retrieve order bold fulfillment status.
     *
     * @return string
     */
    public function getFulfillmentStatus(): string;

    /**
     * @param string $fulfillmentStatus
     * @return void
     */
    public function setFulfillmentStatus(string $fulfillmentStatus): void;

    /**
     * Retrieve order status.
     *
     * @return string
     */
    public function getOrderStatus(): string;

    /**
     * @param string $orderStatus
     * @return void
     */
    public function setOrderStatus(string $orderStatus): void;

    /**
     * Retrieve order number.
     *
     * @return string
     */
    public function getOrderNumber(): string;

    /**
     * @param string $orderNumber
     * @return void
     */
    public function setOrderNumber(string $orderNumber): void;

    /**
     * Retrieve bold order total.
     *
     * @return float
     */
    public function getTotal(): float;

    /**
     * @param float $total
     * @return void
     */
    public function setTotal(float $total): void;

    /**
     * Retrieve request billing address.
     *
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function getBillingAddress(): AddressInterface;


    /**
     * Retrieve request billing address.
     *
     * @param AddressInterface $address
     * @return void
     */
    public function setBillingAddress(AddressInterface $address): void;

    /**
     * Retrieve order payment request data.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function getPayment(): \Magento\Sales\Api\Data\OrderPaymentInterface ;

    /**
     * Retrieve order payment request data.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function setPayment(\Magento\Sales\Api\Data\OrderPaymentInterface $payment): void;

    /**
     * Retrieve payment transaction request data.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface
     */
    public function getTransaction(): TransactionInterface;

    /**
     * @param TransactionInterface $transaction
     * @return void
     */
    public function setTransaction(TransactionInterface $transaction): void;


    /**
     * Retrieve order request extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OrderDataExtensionInterface;

    /**
     * @param OrderDataExtensionInterface $orderDataExtension
     * @return void
     */
    public function setExtensionAttributes(OrderDataExtensionInterface $orderDataExtension): void;
}
