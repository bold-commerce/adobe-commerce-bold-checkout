<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\CustomerEmailValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer email validation result interface.
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
     * @return \Bold\Checkout\Api\Data\CustomerEmailValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
