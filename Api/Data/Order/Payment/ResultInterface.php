<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Interface for the data model encapsulating the response data from an order payment update request.
 *
 * This interface plays a key role in structuring the response data for the '/V1/shops/:shopId/payments' endpoint
 * in the Bold Checkout API. It is designed to provide a comprehensive representation of the outcome of an
 * order payment update request, including details about the updated payment, any errors that occurred during
 * the update process, and extension attributes for enhanced customization.
 *
 * Essential functionalities and data provided by this interface include:
 *  - `getPayment()`: Retrieves the OrderPaymentInterface object from the response, representing the updated
 *    payment details after a successful update operation.
 *  - `getErrors()`: Obtains an array of ErrorInterface objects, detailing any errors encountered during the
 *    payment update process.
 *  - `getExtensionAttributes()`: Offers access to additional, potentially future fields added to the payment
 *    update result, allowing for extended flexibility and adaptability for custom implementations and API evolution.
 *
 * The interface's design aligns with Magento's standards for API responses, ensuring a consistent and
 * reliable way to communicate the results of payment update requests within the Bold Checkout system and
 * Magento's framework.
 *
 * @see \Bold\Checkout\Api\Order\UpdatePaymentInterface::update() for the API endpoint handling payment updates.
 * @see \Bold\Checkout\Api\Data\Order\Payment\ResultExtensionInterface for potential additional response attributes.
 * @see Bold/Checkout/etc/webapi.xml for detailed API endpoint information.
 * @api
 */

interface ResultInterface
{
    /**
     * Get payment object from the response.
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
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in update payment result, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
