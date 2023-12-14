<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client\Response;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data structure representing an error in Bold Checkout API responses.
 *
 * This interface defines the standard format for errors returned by various endpoints
 * of the Bold Checkout API. It encapsulates critical error information including the
 * error code, type, and message. This standardized error structure is crucial for
 * effective error handling and debugging in integrations with the Bold Checkout system.
 *
 * Main functionalities of this interface include:
 *  - `getCode()`: Provides the error code, typically an integer, that identifies the specific error.
 *  - `getType()`: Returns the type of error, generally a short string or identifier.
 *  - `getMessage()`: Delivers a human-readable message describing the error, useful for logging and debugging.
 *  - `getExtensionAttributes()`: Offers a method to access additional fields that might be included
 *    in the error data for future API extensions or custom integrations.
 *
 * As an extension of the Magento\Framework\Api\ExtensibleDataInterface, this interface ensures flexibility
 * and extendibility, aligning with Magento's standards for API data structures.
 *
 * @see \Bold\Checkout\Api\Data\Http\Client\ResultInterface::getErrors() for error retrieval in general API results.
 * @see \Bold\Checkout\Api\Data\PlaceOrder\ResultInterface::getErrors() for error handling in order placement.
 * @see \Bold\Checkout\Api\Data\Order\Payment\ResultInterface::getErrors() for error handling in payment update.
 * @see \Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface::getErrors() for error handling in cart inventory verification.
 * @see \Bold\Checkout\Api\Data\Quote\ResultInterface::getErrors() for error handling in various cart opretions.
 * @see \Bold\Checkout\Api\Data\RegisterSharedSecret\ResultInterface::getErrors() for error handling in registering shared secret.
 * @see \Bold\Checkout\Api\Data\Http\Client\Response\ErrorExtensionInterface for extended error attributes.
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
     * Retrieve error extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in error result, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ErrorExtensionInterface;
}
