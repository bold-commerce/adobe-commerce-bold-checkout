<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder\Request;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Interface for the data model representing the structure of a request to place an order through the Bold Checkout API.
 *
 * This interface is essential for constructing the request payload for the '/V1/shops/:shopId/orders' endpoint,
 * facilitating the mapping of order-related data from Bold Checkout to Magento's order processing system. It
 * standardizes the format of order data, encompassing various elements like quote ID, customer browser IP,
 * payment and transaction details, and other relevant order information.
 *
 * Key functionalities and data elements encompassed by this interface include:
 *  - Methods for setting and getting the quote ID, browser IP, public ID, order number, and other order-specific details.
 *  - `getPayment()` and `setPayment()`: Access and define the OrderPaymentInterface object, which contains payment details for the order.
 *  - `getTransaction()` and `setTransaction()`: Manage the TransactionInterface object, detailing transaction information.
 *  - `getExtensionAttributes()` and `setExtensionAttributes()`: Allow the inclusion and retrieval of additional custom fields through extension attributes,
 *    ensuring the interface's adaptability for future enhancements and custom requirements.
 *
 * This interface addresses constructor injection issues found in earlier Magento 2.3.x versions, offering a reliable and consistent approach
 * to handling order data for processing by Magento via the Bold Checkout system.
 *
 * @see \Bold\Checkout\Api\PlaceOrderInterface::place() for the API endpoint handling this order placement request.
 * @see \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface for potential additional fields in the order data request.
 * @see Bold/Checkout/etc/webapi.xml for detailed API endpoint configuration.
 * @api
 */
interface OrderDataInterface
{
    /**
     * Order data properties used for validation.
     *
     * @see \Bold\Checkout\Model\Http\Client\Request\Validator\OrderPayloadValidator::$requiredProperties
     */
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
    ];

    /**
     * Retrieve cart id.
     *
     * @return int
     */
    public function getQuoteId(): ?int;

    /**
     * Set cart id.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
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
     * Set customer browser ip.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
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
     * Set order bold public id.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param string $publicId
     * @return void
     */
    public function setPublicId(string $publicId): void;

    /**
     * Retrieve order bold financial status.
     *
     * @return string
     * @deprecated
     */
    public function getFinancialStatus(): ?string;

    /**
     * Set order bold financial status.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param string $financialStatus
     * @return void
     * @deprecated
     */
    public function setFinancialStatus(string $financialStatus): void;

    /**
     * Retrieve order bold fulfillment status.
     *
     * @return string
     * @deprecated
     */
    public function getFulfillmentStatus(): ?string;

    /**
     * Set order bold fulfillment status.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param string $fulfillmentStatus
     * @return void
     * @deprecated
     */
    public function setFulfillmentStatus(string $fulfillmentStatus): void;

    /**
     * Retrieve order status.
     *
     * @return string
     * @deprecated
     */
    public function getOrderStatus(): ?string;

    /**
     * Set order status.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param string $orderStatus
     * @return void
     * @deprecated
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
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param string $orderNumber
     * @return void
     */
    public function setOrderNumber(string $orderNumber): void;

    /**
     * Retrieve bold order total.
     *
     * @return float
     * @deprecated
     */
    public function getTotal(): ?float;

    /**
     * Set bold order total.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param float $total
     * @return void
     * @deprecated
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
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
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
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param TransactionInterface $transaction
     * @return void
     */
    public function setTransaction(TransactionInterface $transaction): void;

    /**
     * Retrieve order request extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in place order request object, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?OrderDataExtensionInterface;

    /**
     * Set order request extension attributes.
     *
     * Used instead of constructor injection because
     * of incorrect constructor mapping in earlier versions of Magento 2.3.x.
     *
     * @param \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface $orderDataExtension
     * @return void
     */
    public function setExtensionAttributes(OrderDataExtensionInterface $orderDataExtension): void;
}
