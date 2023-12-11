<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\CustomerEmailValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer email validation result interface.
 *
 * Represents the result of a customer email address validation JSON result
 * used in the /V1/shops/:shopId/customer/email/validate endpoint. @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\CustomerEmailValidatorInterface::validate()
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
     * Retrieve validation result extension attributes. Used in case additional fields are returned by the API.
     *
     * @return \Bold\Checkout\Api\Data\CustomerEmailValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
