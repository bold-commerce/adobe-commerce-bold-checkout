<?php
declare(strict_types=1);

namespace Bold\Platform\Api\Data\AddSharedSecret;

use Bold\Platform\Api\Data\AddSharedSecret\ResultExtensionInterface;
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
     * Retrieve errors.
     *
     * @return \Bold\Checkout\Api\Data\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retreive result extension attributes.
     *
     * @return \Bold\Platform\Api\Data\AddSharedSecret\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
