<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Item;

use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Determines if the cart item should appear in the cart sent to Bold
 */
class Validator
{
    /**
     * Determines if given cart item should appear in the cart.
     *
     * @param CartItemInterface $cartItem
     * @return bool
     */
    public function shouldAppearInCart(CartItemInterface $cartItem): bool
    {
        $parentItem = $cartItem->getParentItem();
        $parentIsBundle = $parentItem && $parentItem->getProductType() === Bundle::TYPE_CODE;
        return (!$cartItem->getChildren() && !$parentIsBundle) || $cartItem->getProductType() === Bundle::TYPE_CODE;
    }
}
