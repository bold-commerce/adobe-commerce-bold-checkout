<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Category;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish category ids for delete observer.
 */
class CategoryDelete implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.delete.categories';

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
     * Publish category delete message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $websiteIds = array_map(
            function (WebsiteInterface $website) {
                return (int)$website->getId();
            },
            $this->storeManager->getWebsites()
        );
        $linkField = $this->metadataPool->getMetadata(CategoryInterface::class)->getLinkField();
        $entityId = (int)$category->getData($linkField);
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
