<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish customer ids for sync observer.
 */
class CustomerAddressSave implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.sync.customers';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param StoreManagerInterface $storeManager
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        EntitySyncPublisher $publisher,
        LoggerInterface $logger,
        MetadataPool $metadataPool
    ) {
        $this->publisher = $publisher;
        $this->storeManager = $storeManager;
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
        $websiteIds = [];
        /** @var \Magento\Customer\Model\Address $address */
        $address = $observer->getEvent()->getCustomerAddress();
        $customer = $address->getCustomer();
        try {
            if (!(int)$customer->getWebsiteId()) {
                $websites = $this->storeManager->getWebsites();
                foreach ($websites as $website) {
                    $websiteIds[] = (int)$website->getId();
                }
            }
            $websiteIds = $websiteIds ?: [(int)$customer->getWebsiteId()];
            $linkField = $this->metadataPool->getMetadata(CustomerInterface::class)->getLinkField();
            $entityId = (int)$customer->getData($linkField);
            foreach ($websiteIds as $websiteId) {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [$entityId]);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
