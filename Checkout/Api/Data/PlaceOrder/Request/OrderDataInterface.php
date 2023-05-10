<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder\Request;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Request order data interface.
 */
interface OrderDataInterface
{
    public const PROPERTY_QUOTE_ID = 'quoteId';
    public const PROPERTY_BROWSER_IP = 'browserIp';
    public const PROPERTY_PUBLIC_ID = 'publicId';
    public const PROPERTY_FINANCIAL_STATUS = 'financialStatus';
    public const PROPERTY_FULFILLMENT_STATUS = 'fulfillmentStatus';
    public const PROPERTY_ORDER_STATUS = 'orderStatus';
    public const PROPERTY_ORDER_NUMBER = 'orderNumber';
    public const PROPERTY_TOTAL = 'total';
    public const PROPERTY_PAYMENT = 'payment';
    public const PROPERTY_TRANSACTION = 'transaction';
    public const PROPERTY_EXTENSION_ATTRIBUTES = 'extension_attributes';
    public const PROPERTIES_REQUIRED = [
        self::PROPERTY_QUOTE_ID,
        self::PROPERTY_BROWSER_IP,
        self::PROPERTY_PUBLIC_ID,
        self::PROPERTY_FINANCIAL_STATUS,
        self::PROPERTY_FULFILLMENT_STATUS,
        self::PROPERTY_ORDER_STATUS,
        self::PROPERTY_ORDER_NUMBER,
        self::PROPERTY_TOTAL,
        self::PROPERTY_PAYMENT,
        self::PROPERTY_TRANSACTION,
    ];

    /**
     * Retrieve cart id.
     *
     * @return int
     */
    public function getQuoteId(): ?int;

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
    public function getBrowserIp(): ?string;

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
    public function getPublicId(): ?string;

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
    public function getFinancialStatus(): ?string;

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
    public function getFulfillmentStatus(): ?string;

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
    public function getOrderStatus(): ?string;

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
    public function getOrderNumber(): ?string;

    /**
     * Set order number.
     *
     * @param string $orderNumber
     * @return void
     */
    public function setOrderNumber(string $orderNumber): void;

    /**
     * Retrieve bold order total.
     *
     * @return float
     */
    public function getTotal(): ?float;

    /**
     * Set bold order total.
     *
     * @param float $total
     * @return void
     */
    public function setTotal(float $total): void;

    /**
     * Retrieve order payment request data.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface
     */
    public function getPayment(): ?OrderPaymentInterface;

    /**
     * Set order payment request data.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return void
     */
    public function setPayment(OrderPaymentInterface $payment): void;

    /**
     * Retrieve payment transaction request data.
     *
     * @return \Magento\Sales\Api\Data\TransactionInterface
     */
    public function getTransaction(): ?TransactionInterface;

    /**
     * Set payment transaction request data.
     *
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
     * Set order request extension attributes.
     *
     * @param OrderDataExtensionInterface $orderDataExtension
     * @return void
     */
    public function setExtensionAttributes(OrderDataExtensionInterface $orderDataExtension): void;
}
