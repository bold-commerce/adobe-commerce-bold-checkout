<?php

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Pool for Bold order initialization is availability checkers.
 */
class IsOrderInitializationAllowedPool implements IsOrderInitializationAllowedInterface
{
    private array $elements;

    /**
     * @param array $elements
     */
    public function __construct(
        array $elements = []
    ) {
        $this->elements = $elements;
    }

    /**
     * Check if Bold order initialization is available.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool
    {
        return array_reduce(
            $this->elements,
            function (bool $allowed, IsOrderInitializationAllowedInterface $item) use ($quote, $request) {
                return $allowed && $item->isAllowed($quote, $request);
            },
            true
        );
    }
}
