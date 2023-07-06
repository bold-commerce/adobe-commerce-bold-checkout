<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Queue;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Queue request data model interface.
 */
interface RequestInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve website id to sync.
     *
     * @return int
     */
    public function getWebsiteId(): int;

    /**
     * Retrieve entity ids to sync.
     *
     * @return array
     */
    public function getEntityIds(): array;

    /**
     * @return \Bold\Checkout\Model\Queue\RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface;
}
