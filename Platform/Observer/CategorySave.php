<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish category ids for sync observer.
 */
class CategorySave implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.sync.categories';

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
     * Publish category sync message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $websiteIds = [];
        $category = $observer->getEvent()->getCategory();
        if (!(int)$category->getStore()->getWebsiteId()) {
            $websites = $this->storeManager->getWebsites();
            foreach ($websites as $website) {
                $websiteIds[] = (int)$website->getId();
            }
        }
        $websiteIds = $websiteIds ?: [(int)$category->getStore()->getWebsiteId()];
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [(int)$category->getId()]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
