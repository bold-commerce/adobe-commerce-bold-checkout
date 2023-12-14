<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data model representing the result of HTTPS requests made to the Bold Checkout API.
 *
 * This interface defines the structure of the response received from various API calls within the Bold Checkout
 * system, including POST, GET, PUT, DELETE, and PATCH methods. It serves as a standardized format for handling
 * and processing API responses, ensuring consistency and ease of integration for various API interactions.
 *
 * Key functionalities and data provided by this interface include:
 *  - `getStatus()`: Returns the HTTP status code of the response, indicative of the request's success or failure.
 *  - `getErrors()`: Retrieves an array of errors, if any, that occurred during the API request.
 *  - `getBody()`: Provides the body of the response, typically containing the main data payload or result of the request.
 *  - `getExtensionAttributes()`: Offers a method to access additional fields that might be added to the response
 *    data in future API versions or custom integrations, enhancing the API's flexibility and extensibility.
 *
 * As an extension of Magento\Framework\Api\ExtensibleDataInterface, this interface aligns with Magento's standards
 * for extensible and adaptable API data structures, making it a robust solution for handling HTTPS responses from
 * the Bold Checkout API.
 *
 * @see \Bold\Checkout\Api\Http\ClientInterface for the different HTTP methods used in API calls.
 * @see \Bold\Checkout\Api\Data\Http\Client\ResultExtensionInterface for extended response attributes.
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve response status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Retrieve response errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Retrieve response body.
     *
     * @return array
     */
    public function getBody(): array;

    /**
     * Retrieve response extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in result data, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
