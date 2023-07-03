<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Catalog\GetProducts;

use Bold\Checkout\Api\Data\Catalog\GetProductsResultInterface;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Get products result.
 */
class Result implements GetProductsResultInterface
{
    /**
     * @var array
     */
    private $products;

    /**
     * @var array
     */
    private $errors;

    /**
     * @param ProductInterface[] $products
     * @param ErrorInterface[] $errors
     */
    public function __construct(array $products = [], array $errors = [])
    {
        $this->products = $products;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getProducts(): array
    {
        return $this->products;
    }
}
