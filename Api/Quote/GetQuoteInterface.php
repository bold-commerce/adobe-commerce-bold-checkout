<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;

/**
 * Set shipping method for quote.
 */
interface GetQuoteInterface
{
    /**
     * Gets a quote.
     *
     * @param string $shopId
     * @param int $cartId
     * @return \Bold\Checkout\Api\Data\Quote\ResultInterface
     */
    public function getQuote(
        string $shopId,
        int $cartId
    ): ResultInterface;
}
