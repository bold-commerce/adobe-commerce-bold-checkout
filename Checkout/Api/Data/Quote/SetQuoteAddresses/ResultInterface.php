<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\SetQuoteAddresses;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Quote response interface.
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Get quote.
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function getQuote(): CartInterface;

    /**
     * Get response extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\Quote\SetQuoteAddresses\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
