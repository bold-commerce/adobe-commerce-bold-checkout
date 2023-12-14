<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\RegisterSharedSecret;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Shared secret result data interface.
 *
 * Represents a response data from the /V1/shops/:shopId/secret/register endpoint. @see Bold/Checkout/etc/webapi.xml
 *
 * @see \Bold\Checkout\Api\RegisterSharedSecretInterface::register()
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve shop id shared secret belongs to.
     *
     * @return string|null
     */
    public function getShopId(): ?string;

    /**
     * Retrieve website code shared secret belongs to.
     *
     * @return string|null
     */
    public function getWebsiteCode(): ?string;

    /**
     * Retrieve website id shared secret belongs to.
     *
     * @return int|null
     */
    public function getWebsiteId(): ?int;

    /**
     * Retrieve module version
     *
     * @return string|null
     */
    public function getModuleVersion(): ?string;

    /**
     * Retrieve errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve result extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures in Magento. This method provides a getter for these
     * additional fields in register share secret result data, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\RegisterSharedSecret\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
