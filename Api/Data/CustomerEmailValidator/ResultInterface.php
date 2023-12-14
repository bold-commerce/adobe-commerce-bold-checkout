<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\CustomerEmailValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data model representing the result of customer email address validation.
 *
 * Used within the customer email validation process, specifically at the '/V1/shops/:shopId/customer/email/validate'
 *
 * Key functionalities provided by this interface include:
 *  - `isValid()`: Determines whether the provided email address is valid.
 *  - `getErrors()`: Retrieves an array of ErrorInterface objects representing any validation errors.
 *  - `getExtensionAttributes()`: Accesses additional, potentially future fields added to the email
 *    validation result, enhancing the adaptability and customization capabilities of the API.
 *
 * @see \Bold\Checkout\Api\CustomerEmailValidatorInterface::validate() for the email validation process.
 * @see \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface for the structure of validation errors.
 * @see \Bold\Checkout\Api\Data\CustomerEmailValidator\ResultExtensionInterface for additional extension attributes.
 * @see Bold/Checkout/etc/webapi.xml for detailed API endpoint information.
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Get is email valid.
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
     * Retrieve validation result extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in customer email validation result, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\CustomerEmailValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
