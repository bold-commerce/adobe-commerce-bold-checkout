<?php

declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);

$currentArea = $registry->registry('isSecureArea');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
    $customer = $customerRepository->get('TestCustomer@example.com');
    $customerRepository->delete($customer);
} catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
    // do nothing
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', $currentArea);
