<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish customer ids for sync observer.
 */
class CustomerSave implements ObserverInterface
{
    private const SYNC_TOPIC_NAME = 'bold.checkout.sync.customers';
    private const DELETE_TOPIC_NAME = 'bold.checkout.delete.customers';

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
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        EntitySyncPublisher $publisher,
        LoggerInterface     $logger,
        MetadataPool        $metadataPool
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Publish customer sync message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $syncWebsiteId = (int)$customer->getData('website_id');
        $previousWebsiteId = (int)$customer->getOrigData('website_id');
        $linkField = $this->metadataPool->getMetadata(CustomerInterface::class)->getLinkField();
        $entityId = (int)$customer->getData($linkField);
        try {
            $this->publisher->publish(self::SYNC_TOPIC_NAME, $syncWebsiteId, [$entityId]);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        if ($syncWebsiteId !== $previousWebsiteId) {
            try {
                $this->publisher->publish(self::DELETE_TOPIC_NAME, $previousWebsiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
