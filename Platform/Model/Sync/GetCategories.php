<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Sync;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load and return filtered list of Categories to update on Bold.
 */
class GetCategories
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager;
        $this->collectionFactory = $collectionFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Load and return filtered list of Categories to update on Bold.
     *
     * @param int[] $entityIds
     * @return CategoryInterface[]
     * @throws LocalizedException
     */
    public function getItems(int $websiteId, array $entityIds): array
    {
        $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $collection = $this->collectionFactory->create();
        $collection->addIdFilter($entityIds);
        $collection->setStoreId($storeId);
        return array_map(
            function (CategoryInterface $category) {
                return $this->dataObjectProcessor->buildOutputDataArray($category, CategoryInterface::class);
            },
            $collection->getItems()
        );
    }
}
