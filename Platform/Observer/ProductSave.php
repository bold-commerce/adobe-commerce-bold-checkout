<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish product ids for sync observer.
 */
class ProductSave implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.sync.products';

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
     * @param StoreManagerInterface $storeManager
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        EntitySyncPublisher $publisher,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Publish product sync message.
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
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, ProductInterface::class, [$product]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
