<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder;

use Bold\Checkout\Api\Data\Quote\Item\DiscountRuleInterface;
use Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts\DiscountRule\DiscountDataInterfaceFactory;
use Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts\RuleDiscountInterfaceFactory;
use Magento\Quote\Model\Quote\Item;

/**
 * Add 'bold_discounts' extension attribute to quote item.
 */
class AddBoldDiscounts
{
    /**
     * @var DiscountDataInterfaceFactory
     */
    private $discountDataInterfaceFactory;

    /**
     * @var RuleDiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @param DiscountDataInterfaceFactory $discountDataInterfaceFactory
     * @param RuleDiscountInterfaceFactory $discountInterfaceFactory
     */
    public function __construct(
        DiscountDataInterfaceFactory $discountDataInterfaceFactory,
        RuleDiscountInterfaceFactory $discountInterfaceFactory
    ) {
        $this->discountDataInterfaceFactory = $discountDataInterfaceFactory;
        $this->discountInterfaceFactory = $discountInterfaceFactory;
    }

    /**
     * Add 'bold_discounts' extension attribute to quote item.
     *
     * @param Item $item
     * @return void
     */
    public function addExtensionAttribute(Item $item): void
    {
        $appliedRules = $item->getQuote()->getAppliedRuleIds();
        $appliedRuleIds =
            is_array($appliedRules)
                ? $appliedRules
                : (
            is_string($appliedRules) && !empty($appliedRules)
                ? explode(',', $appliedRules)
                : []
            );
        $result = [];
        if (!empty($appliedRuleIds)) {
            $itemDiscount = $this->discountDataInterfaceFactory->create(
                [
                    'amount' => (float)$item->getDiscountAmount(),
                    'baseAmount' => (float)$item->getBaseDiscountAmount(),
                    'originalAmount' => (float)$item->getOriginalDiscountAmount(),
                    'baseOriginalAmount' => (float)$item->getBaseOriginalDiscountAmount(),
                ]
            );
            $ruleLabel = $item->getQuote()->getCouponCode() ?: __('Discount');
            /** @var DiscountRuleInterface $itemDiscount */
            $ruleDiscount = $this->discountInterfaceFactory->create(
                [
                    'discount' => $itemDiscount,
                    'rule' => (string)$ruleLabel,
                    'ruleId' => implode(',', $appliedRuleIds),
                ]
            );
            $result[] = $ruleDiscount;
        }

        $item->getExtensionAttributes()->setBoldDiscounts($result);
    }
}
