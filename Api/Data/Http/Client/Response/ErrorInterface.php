<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client\Response;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Response error data interface.
 *
 * Represents an error returned by the Bold Checkout API.
 * @see \Bold\Checkout\Api\Data\Http\Client\ResultInterface::getErrors()
 * @see \Bold\Checkout\Api\Data\PlaceOrder\ResultInterface::getErrors()
 * @api
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
     * Retrieve error extension attributes. Used in case additional fields are returned by the API.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ErrorExtensionInterface;
}
