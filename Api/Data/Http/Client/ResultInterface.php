<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Https client response data model interface.
 *
 * Represents the response of a Bold Checkout API call.
 * @see \Bold\Checkout\Api\Http\ClientInterface::post()
 * @see \Bold\Checkout\Api\Http\ClientInterface::get()
 * @see \Bold\Checkout\Api\Http\ClientInterface::put()
 * @see \Bold\Checkout\Api\Http\ClientInterface::delete()
 * @see \Bold\Checkout\Api\Http\ClientInterface::patch()
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve response status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Retrieve response errors.
     *
     * @return array
     */
    public function getErrors(): array;

    /**
     * Retrieve response body.
     *
     * @return array
     */
    public function getBody(): array;

    /**
     * Retrieve response extension attributes. Used in case additional fields are returned by the Bold API.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
