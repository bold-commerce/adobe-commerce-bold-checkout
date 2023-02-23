<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer\ProductSynchronizer;

use Bold\Platform\Model\Synchronizer\GetPreparedEntities;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Load and return filtered list of Products to update on Bold.
 */
class GetPreparedProducts implements GetPreparedEntities
{
    private $complexProductTypes;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string[] $complexProductTypes
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder      $searchCriteriaBuilder,
        array                      $complexProductTypes = ['complex']
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->complexProductTypes = $complexProductTypes;
    }

    /**
     * Load and return filtered list of Products to update on Bold.
     *
     * @param int[] $entityIds
     * @return ProductInterface[]
     */
    public function getItems(array $entityIds): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                'entity_id',
                $entityIds,
                'in'
            )
            ->addFilter(
                ProductInterface::TYPE_ID,
                $this->complexProductTypes,
                'nin')
            ->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}
