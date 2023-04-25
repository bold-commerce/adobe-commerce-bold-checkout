<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Sync;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load and return filtered list of Products to update on Bold.
 */
class GetProducts
{
    private $skipProductTypes;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param DataObjectProcessor $dataObjectProcessor
     * @param array $skipProductTypes
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        DataObjectProcessor $dataObjectProcessor,
        array $skipProductTypes
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->skipProductTypes = $skipProductTypes;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Retrieve products by entity ids.
     */
    public function getItems(int $websiteId, array $entityIds): array
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $this->searchCriteriaBuilder->addFilter($linkField, $entityIds, 'in');
        $this->searchCriteriaBuilder->addFilter(ProductInterface::TYPE_ID, $this->skipProductTypes, 'nin');
        $this->searchCriteriaBuilder->addFilter('store_id', $storeId);
        $products = $this->productRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        return array_map(
            function (ProductInterface $product) {
                return $this->dataObjectProcessor->buildOutputDataArray($product, ProductInterface::class);
            },
            $products
        );
    }
}
