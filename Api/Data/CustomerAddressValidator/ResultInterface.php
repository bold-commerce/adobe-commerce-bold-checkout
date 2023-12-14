<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\CustomerAddressValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data model representing the result of a customer address validation.
 *
 * This interface defines the structure of the data returned after validating a customer's address via
 * the Bold Checkout system. It is used within the customer address validation process, specifically
 * at the '/V1/shops/:shopId/customer/address/validate' endpoint. This validation process is crucial
 * for ensuring the accuracy and reliability of customer address data within the Bold Checkout workflow.
 *
 * The primary functionalities provided by this interface include:
 *  - `isValid()`: A method to check if the provided address is valid.
 *  - `getErrors()`: A method to retrieve any validation errors as an array of ErrorInterface objects.
 *  - `getExtensionAttributes()`: A method to access additional fields that may be added to the
 *    validation result data structure in the future, ensuring forward compatibility with new features
 *    and customizations.
 *
 * @see \Bold\Checkout\Api\CustomerAddressValidatorInterface::validate() for the validation process.
 * @see \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface for the structure of validation errors.
 * @see \Bold\Checkout\Api\Data\CustomerAddressValidator\ResultExtensionInterface for the extension attributes of the result.
 * @see Bold/Checkout/etc/webapi.xml for the API endpoint configuration.
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Get is address valid.
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get validation errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve result extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in customer address validation result, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\CustomerAddressValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
