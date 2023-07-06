<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Product\Import;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\CatalogImportExport\Model\Import\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Observes the `catalog_product_import_bunch_save_after` event.
 */
class ProductImportUpdate implements ObserverInterface
{
    private const PRODUCT_TOPIC_NAME = 'bold.checkout.sync.products';
    private const CATEGORY_TOPIC_NAME = 'bold.checkout.sync.categories';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntitySyncPublisher $publisher,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * Observer for catalog_product_import_bunch_save_after.
     *
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $bunch = $observer->getEvent()->getBunch();
        $adapter = $observer->getEvent()->getAdapter();
        $categoryIds = [];
        $websiteIdProductIdMap = [];
        foreach ($bunch as $rowData) {
            $sku = $rowData[Product::COL_SKU];
            $productId = (int)$adapter->getNewSku($sku)['entity_id'];
            $websiteIds = array_map('intval', $adapter->getProductWebsites($sku));
            foreach ($websiteIds as $websiteId) {
                $websiteIdProductIdMap[$websiteId][] = $productId;
            }
            $categoryIds = array_unique(
                array_merge(
                    $categoryIds,
                    array_map('intval', $adapter->getProductCategories($sku))
                )
            );
        }
        foreach ($websiteIdProductIdMap as $websiteId => $productIds) {
            try {
                $this->publisher->publish(self::PRODUCT_TOPIC_NAME, $websiteId, $productIds);
                $this->publisher->publish(self::CATEGORY_TOPIC_NAME, $websiteId, $categoryIds);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
