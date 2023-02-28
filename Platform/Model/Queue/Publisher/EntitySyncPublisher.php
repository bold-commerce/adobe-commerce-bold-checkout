<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Queue\Publisher;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Model\Queue\RequestInterfaceFactory;
use Exception;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Entities sync messages publisher.
 */
class EntitySyncPublisher
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var RequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @param ConfigInterface $config
     * @param PublisherInterface $publisher
     * @param MetadataPool $metadataPool
     * @param RequestInterfaceFactory $requestFactory
     */
    public function __construct(
        ConfigInterface $config,
        PublisherInterface $publisher,
        MetadataPool $metadataPool,
        RequestInterfaceFactory $requestFactory
    ) {
        $this->config = $config;
        $this->publisher = $publisher;
        $this->metadataPool = $metadataPool;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Publish sync message for given entities.
     *
     * @param string $topicName
     * @param int $websiteId
     * @param string $entityType
     * @param array $entities
     * @return void
     * @throws Exception
     */
    public function publish(string $topicName, int $websiteId, string $entityType, array $entities): void
    {
        if (!$this->config->isCheckoutEnabled($websiteId)) {
            return;
        }
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();
        $entityIds = [];
        foreach ($entities as $entity) {
            $entityIds[] = (int)$entity->getData($linkField);
        }
        $request = $this->requestFactory->create(
            [
                'website_id' => $websiteId,
                'entity_ids' => $entityIds,
            ]
        );
        $this->publisher->publish($topicName, $request);
    }
}
