<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Plugin\Checkout\Model\Quote\QuoteAction;

use Bold\Checkout\Model\Quote\QuoteAction\DiscountCart;

/**
 * Allow discount cart action.
 */
class AllowDiscountCartPlugin
{
    /**
     * Allow discount cart action.
     *
     * @param DiscountCart $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(DiscountCart $subject, bool $result): bool
    {
        return true;
    }
}
