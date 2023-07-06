<?php

declare(strict_types=1);

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$currentArea = $registry->registry('isSecureArea');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    /** @var ProductInterface $product */
    $product = $productRepository->get('sample_simple_product');
    $productRepository->delete($product);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    // do nothing
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', $currentArea);
