<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Product;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish product ids for delete observer.
 */
class ProductDelete implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.delete.products';

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
     * Publish product delete message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $websiteIds = [];
        $product = $observer->getEvent()->getProduct();
        if (!(int)$product->getStore()->getWebsiteId()) {
            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                $websiteIds[] = (int)$website->getId();
            }
        }
        $websiteIds = $websiteIds ?: [(int)$product->getStore()->getWebsiteId()];
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $entityId = (int)$product->getData($linkField);
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
