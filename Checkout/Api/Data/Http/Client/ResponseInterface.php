<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Http\Client;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Https client response data model interface.
 */
interface ResponseInterface extends ExtensibleDataInterface
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
     * Retrieve response extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\ResponseExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResponseExtensionInterface;
}
