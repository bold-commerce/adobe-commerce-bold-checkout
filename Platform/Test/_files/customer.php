<?php

declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerInterfaceFactory $customerFactory */
$customerFactory = $objectManager->get(CustomerInterfaceFactory::class);
/** @var $customerRepository \Magento\Customer\Api\CustomerRepositoryInterface */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);

/** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
$customer = $customerFactory->create();
$customer->setWebsiteId(1)
    ->setEmail('TestCustomer@example.com')
    ->setGroupId(1)
    ->setStoreId(1)
    ->setPrefix('Mr.')
    ->setFirstname('John')
    ->setMiddlename('A')
    ->setLastname('Smith')
    ->setSuffix('Esq.')
    ->setDefaultBilling(1)
    ->setDefaultShipping(1)
    ->setTaxvat('12')
    ->setGender(0);
$customerRepository->save($customer);
