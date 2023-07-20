<?php

declare(strict_types=1);

namespace Bold\Checkout\Plugin\Customer\CustomerRepository;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add all website ids to SearchCriteria if website_id = 0.
 */
class AddAllWebsitesPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var int[]
     */
    private $websiteIds;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Add all website ids to SearchCriteria if website_id = 0.
     *
     * @param CustomerRepositoryInterface $subject
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function beforeGetList(CustomerRepositoryInterface $subject, SearchCriteriaInterface $searchCriteria): array
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'website_id' && $filter->getValue() === '0') {
                    $filter->setValue(
                        implode(',', $this->getWebsiteIds())
                    );
                }
            }
        }

        return [$searchCriteria];
    }

    /**
     * Get all website ids.
     *
     * @return int[]
     */
    public function getWebsiteIds(): array
    {
        if (!$this->websiteIds) {
            $this->websiteIds = array_map(
                function (WebsiteInterface $website) {
                    return (int)$website->getId();
                },
                $this->storeManager->getWebsites()
            );
        }

        return $this->websiteIds;
    }
}
