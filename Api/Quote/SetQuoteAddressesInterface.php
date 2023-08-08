<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Set quote addresses.
 */
interface SetQuoteAddressesInterface
{
    /**
     * Set quote addresses.
     *
     * @param string $shopId
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @param \Magento\Quote\Api\Data\AddressInterface|null $shippingAddress
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function setAddresses(
        string $shopId,
        int $cartId,
        AddressInterface $billingAddress = null,
        AddressInterface $shippingAddress = null
    ): ResultInterface;
}
