<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\QuoteAction;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Quote\GetCartLineItems;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Generate cart items discount.
 */
class DiscountLineItem implements QuoteActionInterface
{
    private const TYPE = 'discount_line_items';
    private const DISCOUNT_TYPE_FIXED = 'fixed';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getActionData(CartInterface $cart): array
    {
        $couponCode = $cart->getCouponCode();
        if ($couponCode) {
            $cart->setCouponCode('');
            $cart->setTotalsCollectedFlag(false);
            $cart->collectTotals();
        }

        $itemsData = [];
        foreach ($cart->getAllItems() as $lineItem) {
            /** @var CartItemInterface $lineItem */
            if (
                !GetCartLineItems::shouldAppearInCart($lineItem) ||
                floatval($lineItem->getBaseDiscountAmount()) === 0.0
            ) {
                continue;
            }
            
            $itemsData[] = [
                'type' => self::TYPE,
                'data' => [
                    'line_item_keys' => [$lineItem->getId()],
                    'discount_type' => self::DISCOUNT_TYPE_FIXED,
                    'value' => $lineItem->getProductType() === Bundle::TYPE_CODE
                        ? $this->getDiscountValueForBundledProduct($lineItem)
                        : round($lineItem->getBaseDiscountAmount() * 100 / $lineItem->getQty(), 1),
                    'line_text' => __('Discount'),
                    'discount_source' => 'cart',
                ],
            ];
        }

        if ($couponCode) {
            $cart->setCouponCode($couponCode);
            $cart->setTotalsCollectedFlag(true);
            $cart->collectTotals();
        }

        return $itemsData;
    }

    /**
     * Gets discount value for a bundled product line item
     *
     * @param CartItemInterface $item
     * @return int
     */
    private function getDiscountValueForBundledProduct(CartItemInterface $item): int
    {
        $total = 0;
        foreach ($item->getChildren() as $child) {
            $total += round($child->getBaseDiscountAmount() * 10000);
        }

        return (int) round($total / $item->getQty() / 100, 1);
    }

    /**
     * @inheritdoc
     */
    public function isAllowed(int $websiteId): bool
    {
        if ($this->config->isCheckoutTypeSelfHosted($websiteId)) {
            return false;
        }
        return true;
    }
}
