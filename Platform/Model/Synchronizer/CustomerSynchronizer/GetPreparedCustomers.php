<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Synchronizer\CustomerSynchronizer;

use Bold\Platform\Model\Synchronizer\GetPreparedEntities;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Load and return filtered list of Customers to update on Bold.
 */
class GetPreparedCustomers implements GetPreparedEntities
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder       $searchCriteriaBuilder
    ) {
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Load and return filtered list of Categories to update on Bold.
     *
     * @param array $entityIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getItems(array $entityIds): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                'entity_id',
                $entityIds,
                'in'
            )
            ->create();

        return $this->customerRepository->getList($searchCriteria)->getItems();
    }
}
