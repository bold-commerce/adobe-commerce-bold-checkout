<?php
declare(strict_types=1);

namespace Bold\Platform\Api;

use Bold\Platform\Api\Data\CustomerAddressValidator\ResultInterface;
use Magento\Customer\Api\Data\AddressInterface;

/**
 * Validate customer address interface.
 */
interface CustomerAddressValidatorInterface
{
    /**
     * Validate given customer address.
     *
     * @param string $shopId
     * @param AddressInterface $address
     * @return ResultInterface
     */
    public function validate(string $shopId, AddressInterface $address): ResultInterface;
}
