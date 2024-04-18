<?php

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if Bold order initialization is available.
 */
interface IsOrderInitializationAllowedInterface
{
    /**
     * Check if Bold order initialization is available.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool;
}
