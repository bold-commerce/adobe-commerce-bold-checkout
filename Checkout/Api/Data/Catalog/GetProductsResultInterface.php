<?php

namespace Bold\Checkout\Api\Data\Catalog;

/**
 * Get products result interface.
 */
interface GetProductsResultInterface
{
    /**
     * Get result errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Get result products.
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts(): array;
}
