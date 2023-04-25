<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client\Response;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Response error data interface.
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
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ErrorExtensionInterface;
}
