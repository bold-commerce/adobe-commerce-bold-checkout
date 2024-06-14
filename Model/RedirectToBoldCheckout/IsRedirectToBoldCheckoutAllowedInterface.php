<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if the redirect to Bold checkout allowed.
 */
interface IsRedirectToBoldCheckoutAllowedInterface
{
    /**
     * Check if the redirect to Bold checkout allowed.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool;
}
