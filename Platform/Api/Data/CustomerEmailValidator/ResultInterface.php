<?php
declare(strict_types=1);

namespace Bold\Platform\Api\Data\CustomerEmailValidator;

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
     * @return \Bold\Platform\Api\Data\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve validation result extension attributes.
     *
     * @return \Bold\Platform\Api\Data\CustomerEmailValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
