<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\CustomerAddressValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer address validation result data model interface.
 *
 * Represents the result of a customer address validation JSON result used
 * in the /V1/shops/:shopId/customer/address/validate endpoint. @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\CustomerAddressValidatorInterface::validate()
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
     * Retrieve result extension attributes. Used in case additional fields are returned by the API.
     *
     * @return \Bold\Checkout\Api\Data\CustomerAddressValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
