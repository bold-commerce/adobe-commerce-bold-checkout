<?php

namespace Bold\Checkout\Api\Catalog;

use Bold\Checkout\Api\Data\Catalog\GetProductsResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Get products list with media gallery fallback for the complex products.
 */
interface GetProductsInterface
{
    /**
     * Get product list with media gallery fallback for the complex products.
     *
     * @param string $shopId
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Bold\Checkout\Api\Data\Catalog\GetProductsResultInterface
     */
    public function getList(string $shopId, SearchCriteriaInterface $searchCriteria): GetProductsResultInterface;
}
