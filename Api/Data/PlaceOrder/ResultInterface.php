<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Interface for the data model encapsulating the response data from an order placement request through the Bold Checkout API.
 *
 * This interface is crucial in structuring the response for the '/V1/shops/:shopId/orders' endpoint, providing a standardized
 * format for conveying the outcome of an order placement operation. It includes essential information about the placed order,
 * any errors encountered during the process, and extension attributes for enhanced customization and adaptability.
 *
 * Key functionalities and data provided by this interface include:
 *  - `getOrder()`: Retrieves the OrderInterface object from the response, representing the Magento order details after successful placement.
 *  - `getErrors()`: Obtains an array of ErrorInterface objects, detailing any errors that occurred during the order placement process.
 *  - `getExtensionAttributes()`: Offers a method for accessing additional fields that may be included in future iterations of the API,
 *    enhancing the interface's capability to support custom implementations and evolving API requirements.
 *
 * The design of this interface aligns with Magento's standards for API responses, ensuring a consistent and reliable way of communicating
 * the results of order placement requests within the Bold Checkout system and the Magento framework.
 *
 * @see \Bold\Checkout\Api\PlaceOrderInterface::place() for the API endpoint handling order placement.
 * @see \Bold\Checkout\Api\Data\PlaceOrder\ResultExtensionInterface for potential additional response attributes.
 * @see Bold/Checkout/etc/webapi.xml for detailed API endpoint information.
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve Magento order from response.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder(): ?OrderInterface;

    /**
     * Retrieve errors from response.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve response extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in place order result object, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
