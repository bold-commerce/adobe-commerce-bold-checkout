<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Observes the `catalog_product_import_bunch_delete_after` event.
 */
class ProductImportDelete implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.delete.products';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        EntitySyncPublisher $publisher,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * Observer for catalog_product_import_bunch_delete_after.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getBunch();
        $adapter = $observer->getEvent()->getAdapter();
        $websiteIds = array_map(
            function (WebsiteInterface $website) {
                return (int)$website->getId();
            },
            $this->storeManager->getWebsites()
        );
        $productIds = array_map(
            function (array $rowData) use ($adapter) {
                $sku = strtolower($rowData[Product::COL_SKU]);
                return (int)$adapter->getOldSku()[$sku]['entity_id'];
            },
            $bunch
        );
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, $productIds);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
