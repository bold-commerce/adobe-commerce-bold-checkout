<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer\CategorySynchronizer;

use Bold\Platform\Model\Synchronizer\GetPreparedEntities;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Load and return filtered list of Categories to update on Bold.
 */
class GetPreparedCategories implements GetPreparedEntities
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Load and return filtered list of Categories to update on Bold.
     *
     * @param int[] $entityIds
     * @return CategoryInterface[]
     */
    public function getItems(array $entityIds): array
    {
        $collection = $this->collectionFactory->create()->addIdFilter($entityIds);

        return $collection->getItems();
    }
}
