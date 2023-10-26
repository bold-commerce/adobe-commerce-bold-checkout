<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder;

use Bold\Checkout\Api\Data\DiscountDataInterfaceFactory;
use Bold\Checkout\Api\Data\RuleDiscountInterface;
use Bold\Checkout\Api\Data\RuleDiscountInterfaceFactory;
use Magento\Quote\Model\Quote\Item;

/**
 * Add 'bold_discounts' extension attribute to quote item.
 */
class AddBoldDiscountsExtensionAttribute
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
            $data = [
                'amount' => $item->getDiscountAmount(),
                'base_amount' => $item->getBaseDiscountAmount(),
                'original_amount' => $item->getOriginalDiscountAmount(),
                'base_original_amount' => $item->getBaseOriginalDiscountAmount()
            ];
            $itemDiscount = $this->discountDataInterfaceFactory->create(['data' => $data]);
            $ruleLabel = $item->getQuote()->getCouponCode() ?: __('Discount');
            $data = [
                'discount' => $itemDiscount,
                'rule' => $ruleLabel,
                'rule_id' => implode(',', $appliedRuleIds),
            ];
            /** @var RuleDiscountInterface $itemDiscount */
            $ruleDiscount = $this->discountInterfaceFactory->create(['data' => $data]);
            $result[] = $ruleDiscount;
        }

        $item->getExtensionAttributes()->setBoldDiscounts($result);
    }
}
