<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Plugin\Checkout\Model\Quote\QuoteAction;

use Bold\Checkout\Model\Quote\QuoteAction\DiscountLineItem;

/**
 * Allow discount cart line item plugin.
 */
class AllowDiscountLineItemPlugin
{
    /**
     * Allow discount cart line item action.
     *
     * @param DiscountLineItem $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsAllowed(DiscountLineItem $subject, bool $result): bool
    {
        return true;
    }
}
