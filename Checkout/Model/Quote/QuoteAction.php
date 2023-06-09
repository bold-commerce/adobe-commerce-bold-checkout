<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\Quote\QuoteAction\QuoteActionInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Quote actions generator.
 */
class QuoteAction
{
    /**
     * @var QuoteActionInterface[]
     */
    private $quoteActions;

    /**
     * @param QuoteActionInterface[] $quoteActions
     */
    public function __construct(array $quoteActions)
    {
        $this->quoteActions = $quoteActions;
    }

    /**
     * Generate cart actions data.
     *
     * @param CartInterface $cart
     * @return array
     */
    public function getActionsData(CartInterface $cart): array
    {
        $result = [];
        foreach ($this->quoteActions as $quoteAction) {
            if ($quoteAction->isAllowed((int)$cart->getStore()->getWebsiteId())) {
                $actionData = $quoteAction->getActionData($cart);
                if ($actionData) {
                    $result[] = $actionData;
                }
            }
        }

        return $result;
    }
}
