<?php

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Bold\Checkout\Model\Quote\IsBoldCheckoutAllowedForCart;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if Bold order initialization is available for quote.
 */
class IsOrderInitializationAllowedForCart implements IsOrderInitializationAllowedInterface
{
    private IsBoldCheckoutAllowedForCart $allowedForCart;

    /**
     * @param IsBoldCheckoutAllowedForCart $allowedForCart
     */
    public function __construct(IsBoldCheckoutAllowedForCart $allowedForCart)
    {
        $this->allowedForCart = $allowedForCart;
    }

    /**
     * Check if Bold order initialization is available for quote.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool
    {
        return $this->allowedForCart->isAllowed($quote);
    }
}
