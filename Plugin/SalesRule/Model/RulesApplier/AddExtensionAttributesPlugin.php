<?php

declare(strict_types=1);

namespace Bold\Checkout\Plugin\SalesRule\Model\RulesApplier;

use Bold\Checkout\Api\Data\DiscountDataInterfaceFactory;
use Bold\Checkout\Api\Data\RuleDiscountInterface;
use Bold\Checkout\Api\Data\RuleDiscountInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\DeltaPriceRound;
use Magento\SalesRule\Model\Quote\ChildrenValidationLocator;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory;
use Magento\SalesRule\Model\Rule\Action\Discount\Data;
use Magento\SalesRule\Model\RulesApplier;
use Magento\SalesRule\Model\Utility;

/**
 * Add discounts extension attribute if it is absent.
 */
class AddExtensionAttributesPlugin
{
    /**
     * @var array
     */
    private $itemData = [];

    /**
     * @var array
     */
    private $rulesData = [];

    /**
     * @var array
     */
    private $discountAggregator = [];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Utility
     */
    private $validatorUtility;

    /**
     * @var ChildrenValidationLocator
     */
    private $childrenValidationLocator;

    /**
     * @var CalculatorFactory
     */
    private $calculatorFactory;

    /**
     * @var RuleDiscountInterfaceFactory
     */
    private $discountInterfaceFactory;

    /**
     * @var DiscountDataInterfaceFactory
     */
    private $discountDataInterfaceFactory;

    /**
     * @var DeltaPriceRound
     */
    private $deltaPriceRound;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Utility $validatorUtility
     * @param ChildrenValidationLocator $childrenValidationLocator
     * @param CalculatorFactory $calculatorFactory
     * @param RuleDiscountInterfaceFactory $discountInterfaceFactory
     * @param DiscountDataInterfaceFactory $discountDataInterfaceFactory
     * @param DeltaPriceRound $deltaPriceRound
     */
    public function __construct(
        ObjectManagerInterface       $objectManager,
        Utility                      $validatorUtility,
        ChildrenValidationLocator    $childrenValidationLocator,
        CalculatorFactory            $calculatorFactory,
        RuleDiscountInterfaceFactory $discountInterfaceFactory,
        DiscountDataInterfaceFactory $discountDataInterfaceFactory,
        DeltaPriceRound              $deltaPriceRound
    ) {
        $this->objectManager = $objectManager;
        $this->validatorUtility = $validatorUtility;
        $this->childrenValidationLocator = $childrenValidationLocator;
        $this->calculatorFactory = $calculatorFactory;
        $this->discountInterfaceFactory = $discountInterfaceFactory;
        $this->discountDataInterfaceFactory = $discountDataInterfaceFactory;
        $this->deltaPriceRound = $deltaPriceRound;
    }

    /**
     * Store existing discount amounts.
     *
     * @param RulesApplier $subject
     * @param $item
     * @param $rules
     * @param $skipValidation
     * @param $couponCode
     * @return void
     */
    public function beforeApplyRules(RulesApplier $subject, $item, $rules, $skipValidation, $couponCode)
    {
        $this->backupBeforeItem($item);
        $this->backupBeforeRules($item);
    }

    /**
     * Backup order item data before discounts calculation.
     *
     * @param $item
     * @return void
     */
    private function backupBeforeItem($item): void
    {
        $this->backupItem($item, 'before');
    }

    /**
     * Backup order item data.
     *
     * @param AbstractItem $item
     * @param string $key
     * @return void
     */
    private function backupItem(AbstractItem $item, string $key): void
    {
        $itemId = $item->getId();
        $this->itemData[$key][$itemId] = [
            'discount_amount' => $item->getDiscountAmount(),
            'base_discount_amount' => $item->getBaseDiscountAmount(),
            'discount_percent' => $item->getDiscountPercent(),
        ];
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->backupItem($child, $key);
            }
        }
    }

    /**
     * Backup item discount rules before discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function backupBeforeRules(AbstractItem $item): void
    {
        $this->backupRules($item, 'before');
    }

    /**
     * Backup item discount rules data.
     *
     * @param AbstractItem $item
     * @param string $key
     * @return void
     */
    private function backupRules(AbstractItem $item, string $key): void
    {
        $quote = $item->getQuote();
        $cartRules = $quote->getCartFixedRules();
        $this->rulesData[$key] = $cartRules;
    }

    /**
     * Add discounts extension attribute if it is absent.
     *
     * @param RulesApplier $subject
     * @param array $result
     * @param AbstractItem $item
     * @param array $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     * @return array
     */
    public function afterApplyRules(RulesApplier $subject, $result, $item, $rules, $skipValidation, $couponCode)
    {
        $this->backupAfterItem($item);
        $this->backupAfterRules($item);
        $this->restoreBeforeItem($item);
        $this->restoreBeforeRules($item);
        $address = $item->getAddress();
        $appliedRuleIds = [];
        /* @var $rule Rule */
        foreach ($rules as $rule) {
            $this->deltaPriceRound->reset('CartFixed' . $rule->getId());
            if (!$this->validatorUtility->canProcessRule($rule, $address)) {
                continue;
            }
            if (!$skipValidation && !$rule->getActions()->validate($item)) {
                if (!$this->childrenValidationLocator->isChildrenValidationRequired($item)) {
                    continue;
                }
                $childItems = $item->getChildren();
                $isContinue = true;
                if (!empty($childItems)) {
                    foreach ($childItems as $childItem) {
                        if ($rule->getActions()->validate($childItem)) {
                            $isContinue = false;
                        }
                    }
                }
                if ($isContinue) {
                    continue;
                }
            }

            $this->applyRule($item, $rule, $address);
            $appliedRuleIds[$rule->getRuleId()] = $rule->getRuleId();

            if ($rule->getStopRulesProcessing()) {
                break;
            }
        }
        $this->restoreAfterItem($item);
        $this->restoreAfterRules($item);

        return $result;
    }

    /**
     * Backup order item data after discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function backupAfterItem(AbstractItem $item): void
    {
        $this->backupItem($item, 'after');
    }

    /**
     * Backup item discount rules after discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function backupAfterRules(AbstractItem $item): void
    {
        $this->backupRules($item, 'after');
    }

    /**
     * Restore order item data before discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function restoreBeforeItem(AbstractItem $item): void
    {
        $this->restoreItem($item, 'before');
    }

    /**
     * Restore order item data.
     *
     * @param AbstractItem $item
     * @param string $key
     * @return void
     */
    private function restoreItem(AbstractItem $item, string $key): void
    {
        $itemId = $item->getId();
        $item->setDiscountAmount($this->itemData[$key][$itemId]['discount_amount']);
        $item->setBaseDiscountAmount($this->itemData[$key][$itemId]['base_discount_amount']);
        $item->setDiscountPercent($this->itemData[$key][$itemId]['discount_percent']);
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            foreach ($item->getChildren() as $child) {
                $this->restoreItem($child, $key);
            }
        }
    }

    /**
     * Restore item discount rules before discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function restoreBeforeRules(AbstractItem $item): void
    {
        $this->restoreRules($item, 'before');
    }

    /**
     * Restore item discount rules data.
     *
     * @param AbstractItem $item
     * @param string $key
     * @return void
     */
    private function restoreRules(AbstractItem $item, string $key): void
    {
        $quote = $item->getQuote();
        $cartRules = $this->rulesData[$key];
        $quote->setCartFixedRules($cartRules);
    }

    /**
     * Apply Rule
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     */
    private function applyRule(AbstractItem $item, Rule $rule, Address $address): void
    {
        if ($item->getChildren() && $item->isChildrenCalculated()) {
            $cloneItem = clone $item;
            /**
             * validate without children
             */
            $applyAll = $rule->getActions()->validate($cloneItem);
            foreach ($item->getChildren() as $childItem) {
                if ($applyAll || $rule->getActions()->validate($childItem)) {
                    $this->calculateDiscountData($childItem, $rule, $address);
                }
            }
        } else {
            $this->calculateDiscountData($item, $rule, $address);
        }
    }

    /**
     * Get discount Data
     *
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     */
    private function calculateDiscountData(AbstractItem $item, Rule $rule, Address $address): void
    {
        $qty = $this->validatorUtility->getItemQty($item, $rule);
        $discountCalculator = $this->calculatorFactory->create($rule->getSimpleAction());
        $qty = $discountCalculator->fixQuantity($qty, $rule);
        $discountData = $discountCalculator->calculate($rule, $item, $qty);
        $this->validatorUtility->deltaRoundingFix($discountData, $item);
        $this->calculateDiscountBreakdown($discountData, $item, $rule, $address);
        $this->validatorUtility->minFix($discountData, $item, $qty);
    }

    /**
     * Calculate Discount Breakdown
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     */
    private function calculateDiscountBreakdown(
        Data         $discountData,
        AbstractItem $item,
        Rule         $rule,
        Address      $address
    ): void {
        if ($discountData->getAmount() > 0 && $item->getExtensionAttributes()) {
            $data = [
                'amount' => $discountData->getAmount(),
                'base_amount' => $discountData->getBaseAmount(),
                'original_amount' => $discountData->getOriginalAmount(),
                'base_original_amount' => $discountData->getBaseOriginalAmount()
            ];
            $itemDiscount = $this->discountDataInterfaceFactory->create(['data' => $data]);
            $ruleLabel = $rule->getCouponCode() ?: __('Discount');
            $data = [
                'discount' => $itemDiscount,
                'rule' => $ruleLabel,
                'rule_id' => $rule->getId(),
            ];
            /** @var RuleDiscountInterface $itemDiscount */
            $ruleDiscount = $this->discountInterfaceFactory->create(['data' => $data]);
            $this->discountAggregator[$item->getId()][$rule->getId()] = $ruleDiscount;
            $item->getExtensionAttributes()->setBoldDiscounts(array_values($this->discountAggregator[$item->getId()]));
            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getExtensionAttributes()) {
                $this->aggregateDiscountBreakdown($discountData, $parentItem, $rule, $address);
            }
        }
    }

    /**
     * Add Discount Breakdown to existing discount data.
     *
     * @param Data $discountData
     * @param AbstractItem $item
     * @param Rule $rule
     * @param Address $address
     */
    private function aggregateDiscountBreakdown(
        Data         $discountData,
        AbstractItem $item,
        Rule         $rule,
        Address      $address
    ): void
    {
        $ruleLabel = $rule->getCouponCode() ?: __('Discount');
        /** @var RuleDiscountInterface[] $discounts */
        $discounts = [];
        foreach ((array)$item->getExtensionAttributes()->getBoldDiscounts() as $discount) {
            $discounts[$discount->getRuleID()] = $discount;
        }
        $data = [
            'amount' => $discountData->getAmount(),
            'base_amount' => $discountData->getBaseAmount(),
            'original_amount' => $discountData->getOriginalAmount(),
            'base_original_amount' => $discountData->getBaseOriginalAmount()
        ];
        $discount = $discounts[$rule->getId()] ?? null;
        if (isset($discount)) {
            $data['amount'] += $discount->getDiscountData()->getAmount();
            $data['base_amount'] += $discount->getDiscountData()->getBaseAmount();
            $data['original_amount'] += $discount->getDiscountData()->getOriginalAmount();
            $data['base_original_amount'] += $discount->getDiscountData()->getBaseOriginalAmount();
        }
        $discounts[$rule->getId()] = $this->discountInterfaceFactory->create(
            [
                'data' => [
                    'discount' => $this->discountDataInterfaceFactory->create(['data' => $data]),
                    'rule' => $ruleLabel,
                    'rule_id' => $rule->getId(),
                ]
            ]
        );
        $item->getExtensionAttributes()->setBoldDiscounts(array_values($discounts));
    }

    /**
     * Restore order item data after discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function restoreAfterItem(AbstractItem $item): void
    {
        $this->restoreItem($item, 'after');
    }

    /**
     * Restore item discount rules after discounts calculation.
     *
     * @param AbstractItem $item
     * @return void
     */
    private function restoreAfterRules(AbstractItem $item): void
    {
        $this->restoreRules($item, 'after');
    }

    /**
     * Check if extension attribute exists.
     *
     * @return bool
     */
    private function attributeExists(): bool
    {
        try {
            $result = true;
            $this->objectManager->get(RuleDiscountInterface::class);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
