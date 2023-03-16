<?php
declare(strict_types=1);

namespace Bold\Platform\Api\Data\Response;

use Bold\Platform\Api\Data\Response\ErrorExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Place order response error data interface.
 */
interface ErrorInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve error code.
     *
     * @return int
     */
    public function getCode(): int;

    /**
     * Retrieve error type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retrieve error message.
     *
     * @return string
     */
    public function getMessage(): string;

    /**
     * Retrieve error extension attributes.
     *
     * @return \Bold\Platform\Api\Data\Response\ErrorExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ErrorExtensionInterface;
}
