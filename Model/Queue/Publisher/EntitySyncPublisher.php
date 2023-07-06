<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Queue\Publisher;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Queue\RequestInterfaceFactory;
use Exception;
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
     * @var RequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @param ConfigInterface $config
     * @param PublisherInterface $publisher
     * @param RequestInterfaceFactory $requestFactory
     */
    public function __construct(
        ConfigInterface $config,
        PublisherInterface $publisher,
        RequestInterfaceFactory $requestFactory
    ) {
        $this->config = $config;
        $this->publisher = $publisher;
        $this->requestFactory = $requestFactory;
    }

    /**
     * Publish sync message for given entities.
     *
     * @param string $topicName
     * @param int $websiteId
     * @param int[] $entityIds
     * @return void
     * @throws Exception
     */
    public function publish(string $topicName, int $websiteId, array $entityIds): void
    {
        if (!$this->config->isCheckoutEnabled($websiteId)) {
            return;
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
