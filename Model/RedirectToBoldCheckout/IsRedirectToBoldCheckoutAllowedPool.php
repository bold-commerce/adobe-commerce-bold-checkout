<?php

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if the redirect to Bold checkout allowed pool.
 */
class IsRedirectToBoldCheckoutAllowedPool implements IsRedirectToBoldCheckoutAllowedInterface
{
    private $elementList;

    /**
     * @param IsRedirectToBoldCheckoutAllowedInterface[] $elementList
     */
    public function __construct(
        array $elementList = []
    ) {
        $this->elementList = $elementList;
    }

    /**
     * Check if the redirect to Bold checkout allowed.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool
    {
        return array_reduce(
            $this->elementList,
            function (bool $allowed, IsRedirectToBoldCheckoutAllowedInterface $item) use ($quote, $request) {
                return $allowed && $item->isAllowed($quote, $request);
            },
            true
        );
    }
}
