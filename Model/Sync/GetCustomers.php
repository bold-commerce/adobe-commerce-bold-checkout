<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Sync;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load and return filtered list of Customers to update on Bold.
 */
class GetCustomers
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

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
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Load and return filtered list of Categories to update on Bold.
     *
     * @param int $websiteId
     * @param array $entityIds
     * @return array
     * @throws LocalizedException
     */
    public function getItems(int $websiteId, array $entityIds): array
    {
        $linkField = $this->metadataPool->getMetadata(CustomerInterface::class)->getLinkField();
        $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        $this->searchCriteriaBuilder->addFilter($linkField, $entityIds, 'in');
        $this->searchCriteriaBuilder->addFilter('store_id', $storeId);
        $customers = $this->customerRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        return [
            'items' =>
                array_map(
                    function (CustomerInterface $customer) {
                        return $this->dataObjectProcessor->buildOutputDataArray($customer, CustomerInterface::class);
                    },
                    $customers
                ),
        ];
    }
}
