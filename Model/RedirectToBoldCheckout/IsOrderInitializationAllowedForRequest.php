<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Bold\Checkout\Model\IsBoldCheckoutAllowedForRequest;
use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Check if Bold order initialization is available for request.
 */
class IsOrderInitializationAllowedForRequest implements IsOrderInitializationAllowedInterface
{
    /**
     * @var IsBoldCheckoutAllowedForRequest
     */
    private $allowedForRequest;

    /**
     * @param IsBoldCheckoutAllowedForRequest $allowedForRequest
     */
    public function __construct(IsBoldCheckoutAllowedForRequest $allowedForRequest)
    {
        $this->allowedForRequest = $allowedForRequest;
    }

    /**
     * Check if Bold order initialization is available for request.
     *
     * @param CartInterface $quote
     * @param RequestInterface $request
     * @return bool
     */
    public function isAllowed(CartInterface $quote, RequestInterface $request): bool
    {
        return $this->allowedForRequest->isAllowed($quote, $request);
    }
}
