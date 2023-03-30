<?php
declare(strict_types=1);

namespace Bold\Platform\Api\Data\CustomerAddressValidator;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer address validation result data model interface.
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
     * @return \Bold\Platform\Api\Data\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve result extension attributes.
     *
     * @return \Bold\Platform\Api\Data\CustomerAddressValidator\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
