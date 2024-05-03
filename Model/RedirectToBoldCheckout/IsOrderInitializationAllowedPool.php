<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\RedirectToBoldCheckout;

use Magento\Framework\App\RequestInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Pool for Bold order initialization is availability checkers.
 */
class IsOrderInitializationAllowedPool implements IsOrderInitializationAllowedInterface
{
    /**
     * @var IsOrderInitializationAllowedInterface[]
     */
    private $elementList;

    /**
     * @param IsOrderInitializationAllowedInterface[] $elementList
     */
    public function __construct(
        array $elementList = []
    ) {
        $this->elementList = $elementList;
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
            $this->elementList,
            function (bool $allowed, IsOrderInitializationAllowedInterface $item) use ($quote, $request) {
                return $allowed && $item->isAllowed($quote, $request);
            },
            true
        );
    }
}
