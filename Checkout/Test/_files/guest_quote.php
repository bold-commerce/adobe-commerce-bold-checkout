<?php
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea('frontend');
$storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
$product = Bootstrap::getObjectManager()->get(ProductFactory::class)->create();
$product->setTypeId('simple')
    ->setAttributeSetId(4)
    ->setName('Bold Simple Product')
    ->setSku('bold-simple')
    ->setPrice(10)
    ->setTaxClassId(0)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    )
    ->setWebsiteIds([$storeManager->getStore()->getWebsiteId()]);
$productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
$productRepository->save($product);
$addressData = [
    'region' => 'CA',
    'region_id' => '12',
    'postcode' => '90230',
    'company' => 'Test Company',
    'lastname' => 'Doe',
    'firstname' => 'John',
    'street' => 'street',
    'city' => 'Culver City',
    'email' => 'john.doe@example.com',
    'telephone' => '555-55-555',
    'country_id' => 'US',
];
$shippingAddress = Bootstrap::getObjectManager()->get(AddressFactory::class)->create(['data' => $addressData]);
$shippingAddress->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);
$store = $storeManager->getStore();
$quote = Bootstrap::getObjectManager()->get(QuoteFactory::class)->create();
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('bold_test_reserved_order_id')
    ->setShippingAddress($shippingAddress)
    ->addProduct($product);
$quote->setIsMultiShipping(0);
$quote->collectTotals();
$quoteRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);
$quoteIdMask = Bootstrap::getObjectManager()->get(QuoteIdMaskFactory::class)->create();
$quoteIdMaskResource = Bootstrap::getObjectManager()->get(QuoteIdMask::class);
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMaskResource->save($quoteIdMask);
