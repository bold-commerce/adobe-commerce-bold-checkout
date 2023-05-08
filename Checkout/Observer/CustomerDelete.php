<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish customer ids for delete observer.
 */
class CustomerDelete implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.delete.customers';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var Share
     */
    private $share;

    /**
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     * @param MetadataPool $metadataPool
     * @param Share $share
     */
    public function __construct(
        EntitySyncPublisher $publisher,
        LoggerInterface $logger,
        MetadataPool $metadataPool,
        Share $share
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->metadataPool = $metadataPool;
        $this->share = $share;
    }

    /**
     * Publish customer delete message.
     *
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $websiteIds = $this->share->isWebsiteScope()
            ? [(int)$customer->getWebsiteId()]
            : array_map('intval', $customer->getSharedWebsiteIds());
        $linkField = $this->metadataPool->getMetadata(CustomerInterface::class)->getLinkField();
        $entityId = (int)$customer->getData($linkField);
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}