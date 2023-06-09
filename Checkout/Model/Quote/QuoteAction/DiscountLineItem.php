<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\QuoteAction;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Quote\Api\Data\CartInterface;

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
        $lineItems = [];
        foreach ($cart->getAllItems() as $cartItem) {
            if ($cartItem->getChildren()) {
                continue;
            }
            $lineItems[] = $cartItem;
        }
        foreach ($lineItems as $lineItem) {
            if ((float)$lineItem->getBaseDiscountAmount()) {
                $itemsData[] = [
                    'type' => self::TYPE,
                    'data' => [
                        'line_item_keys' => [$lineItem->getId()],
                        'discount_type' => self::DISCOUNT_TYPE_FIXED,
                        'value' => (int)($lineItem->getBaseDiscountAmount() * 100) / $lineItem->getQty(),
                        'line_text' => __('Discount'),
                        'discount_source' => 'cart',
                    ],
                ];
            }
        }
        if ($couponCode) {
            $cart->setCouponCode($couponCode);
            $cart->setTotalsCollectedFlag(true);
            $cart->collectTotals();
        }
        return $itemsData;
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
