<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Queue;

use Bold\Platform\Model\Queue\RequestExtensionInterface;

/**
 * Queue request data model.
 */
class Request implements RequestInterface
{
    /**
     * @var int
     */
    private $websiteId;

    /**
     * @var array
     */
    private $entityIds;

    /**
     * @var \Bold\Platform\Model\Queue\RequestExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param int $website_id
     * @param int[] $entity_ids
     */
    public function __construct(
        int $website_id,
        array $entity_ids,
        RequestExtensionInterface $extension_attributes = null
    ) {
        $this->websiteId = $website_id;
        $this->entityIds = $entity_ids;
        $this->extensionAttributes = $extension_attributes;
    }

    /**
     * @inheritDoc
     */
    public function getWebsiteId(): int
    {
        return $this->websiteId;
    }

    /**
     * @inheritDoc
     */
    public function getEntityIds(): array
    {
        return $this->entityIds;
    }

    /**
     * @return RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
