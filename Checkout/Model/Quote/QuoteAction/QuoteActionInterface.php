<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\QuoteAction;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Generate cart actions service interface.
 */
interface QuoteActionInterface
{
    /**
     * Generate cart action data.
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getActionData(CartInterface $cart): array;
}
