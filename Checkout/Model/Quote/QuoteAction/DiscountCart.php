<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\QuoteAction;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Generate general cart discount.
 */
class DiscountCart implements QuoteActionInterface
{
    private const TYPE = 'DISCOUNT_CART';
    private const DISCOUNT_TYPE = 'fixed';

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
        $discountAmount = ($cart->getBaseSubtotal() - $cart->getBaseSubtotalWithDiscount());
        $couponDiscount = 0;
        $couponCode = $cart->getCouponCode();
        if ($couponCode) {
            $this->recollectTotalsWithCoupon($cart, '');
            $noCouponDiscount = ($cart->getBaseSubtotal() - $cart->getBaseSubtotalWithDiscount());
            $couponDiscount = $discountAmount - $noCouponDiscount;
            $this->recollectTotalsWithCoupon($cart, $couponCode);
        }

        return [
            [
                'type' => self::TYPE,
                'data' => [
                    'discountType' => self::DISCOUNT_TYPE,
                    'discountAmount' => $couponDiscount * 100.0,
                    'transformationMessage' => $couponCode,
                    'platform_display_text' => $couponCode,
                    'discount_source' => 'coupon',
                ],
            ],
        ];
    }

    /**
     * Set new Coupon Code to Quote and recollect totals.
     *
     * @param CartInterface $quote
     * @param string $couponCode
     * @return void
     */
    private function recollectTotalsWithCoupon(CartInterface $quote, string $couponCode): void
    {
        $quote->setCouponCode($couponCode);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals();
    }

    /**
     * @inheritDoc
     */
    public function isAllowed(int $websiteId): bool
    {
        if ($this->config->isCheckoutTypeSelfHosted($websiteId)) {
            return false;
        }
        return true;
    }
}
