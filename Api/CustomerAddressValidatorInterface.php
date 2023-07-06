<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

use Bold\Checkout\Api\Data\CustomerAddressValidator\ResultInterface;
use Magento\Customer\Api\Data\AddressInterface;

/**
 * Validate customer address.
 */
interface CustomerAddressValidatorInterface
{
    /**
     * Validate given customer address.
     *
     * @param string $shopId
     * @param AddressInterface $address
     * @return \Bold\Checkout\Api\Data\CustomerAddressValidator\ResultInterface
     */
    public function validate(string $shopId, AddressInterface $address): ResultInterface;
}
