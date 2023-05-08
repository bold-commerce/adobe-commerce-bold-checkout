<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Set quote addresses service interface.
 */
interface SetQuoteAddressesInterface
{
    /**
     * Set quote addresses.
     *
     * @param string $shopId
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @param \Magento\Quote\Api\Data\AddressInterface|null $shippingAddress
     * @return \Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultInterface
     */
    public function setAddresses(
        string $shopId,
        int $cartId,
        AddressInterface $billingAddress,
        AddressInterface $shippingAddress = null
    ): ResultInterface;
}
