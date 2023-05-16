<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\RegisterSharedSecret;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Add shred secret result data interface.
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
     * Retrieve errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve result extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\RegisterSharedSecret\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
