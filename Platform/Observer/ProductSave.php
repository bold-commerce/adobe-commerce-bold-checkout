<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish product ids for sync observer.
 */
class ProductSave implements ObserverInterface
{
    private const SYNC_TOPIC_NAME = 'bold.checkout.sync.products';
    private const DELETE_TOPIC_NAME = 'bold.checkout.delete.products';

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
        EntitySyncPublisher   $publisher,
        LoggerInterface       $logger,
        MetadataPool          $metadataPool
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Publish product sync message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $syncWebsiteIds = array_map('intval', $product->getData('website_ids'));
        $previousWebsiteIds = array_map('intval', $product->getOrigData('website_ids'));
        $deleteWebsiteIds = array_diff($previousWebsiteIds, $syncWebsiteIds);
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $entityId = (int)$product->getData($linkField);
        foreach ($syncWebsiteIds as $syncWebsiteId) {
            try {
                $this->publisher->publish(self::SYNC_TOPIC_NAME, $syncWebsiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
        foreach ($deleteWebsiteIds as $deleteWebsiteId) {
            try {
                $this->publisher->publish(self::DELETE_TOPIC_NAME, $deleteWebsiteId, [$entityId]);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}

