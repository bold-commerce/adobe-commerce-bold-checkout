<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Quote response data interface.
 *
 * Represents a response data from the /V1/shops/:shopId/cart/:cartId, /V1/shops/:shopId/cart/:cartId/addresses,
 * /V1/shops/:shopId/cart/:cartId/shippingMethod and /V1/shops/:shopId/cart/:cartId/coupons endpoints.
 *
 * @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\Quote\GetQuoteInterface::getQuote()
 * @see \Bold\Checkout\Api\Quote\SetQuoteAddressesInterface::setAddresses()
 * @see \Bold\Checkout\Api\Quote\SetQuoteShippingMethodInterface::setShippingMethod()
 * @see \Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface::setCoupon()
 * @see \Bold\Checkout\Api\Quote\RemoveQuoteCouponCodeInterface::removeCoupon()
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Get quote.
     *
     * @return \Magento\Quote\Api\Data\CartInterface|null
     */
    public function getQuote(): ?CartInterface;

    /**
     * Get quote totals.
     *
     * @return \Magento\Quote\Api\Data\TotalsInterface|null
     */
    public function getTotals(): ?TotalsInterface;

    /**
     * Get quote shipping rates.
     *
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethods(): array;

    /**
     * Get quote errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Get response extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures in Magento. This method provides a getter for these
     * additional fields in quote result data, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Quote\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
