<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\SetQuoteAddresses;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\TotalsInterface;

/**
 * Add addresses to quote response interface.
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
     * Get set quote addresses errors.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Get response extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
