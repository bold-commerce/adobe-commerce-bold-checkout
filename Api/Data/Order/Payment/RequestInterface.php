<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Interface for the data model representing the request structure for updating an order's payment information.
 *
 * This interface is crucial in structuring the data sent in API requests to the '/V1/shops/:shopId/payments' endpoint
 * of the Bold Checkout API. It is designed to encapsulate all necessary details required to update the payment
 * information of an order, including the payment and transaction details. This standardized request format is
 * essential for the consistent and accurate processing of payment updates in the Bold Checkout system.
 *
 * Key functionalities and data encapsulated by this interface include:
 *  - `setPayment()`: Allows setting the OrderPaymentInterface object, representing the payment details to be updated.
 *  - `getPayment()`: Retrieves the set payment object from the request.
 *  - `setTransaction()`: Enables setting the TransactionInterface object, providing transaction-related information.
 *  - `getTransaction()`: Obtains the set transaction object from the request.
 *  - `setExtensionAttributes()`: Facilitates adding additional, custom fields to the request through extension attributes,
 *    enhancing flexibility for future API developments and custom needs.
 *  - `getExtensionAttributes()`: Retrieves any set extension attributes from the request.
 *
 * This interface addresses constructor mapping issues encountered in earlier versions of Magento 2.3.x, offering
 * a reliable and consistent way to handle payment update requests within Magento's framework and the Bold Checkout API.
 *
 * @see \Bold\Checkout\Api\Order\UpdatePaymentInterface::update() for the API endpoint handling this request data.
 * @see \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface for potential additional fields in the request.
 * @see Bold/Checkout/etc/webapi.xml for detailed API endpoint configuration.
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
     * Get extension attributes from the request.
     *
     *  Extension attributes are new, optional fields that can be added to existing
     *  API data structures. This method provides a getter for these
     *  additional fields in update payment request object, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface;
}
