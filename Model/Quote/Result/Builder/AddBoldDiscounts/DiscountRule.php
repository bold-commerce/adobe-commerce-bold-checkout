<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts;

use Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataInterface;
use Bold\Checkout\Api\Data\Quote\Item\DiscountRuleExtensionInterface;
use Bold\Checkout\Api\Data\Quote\Item\DiscountRuleInterface;

/**
 * Quote item rule discount model. Used to add 'bold_discounts' extension attribute to quote item.
 *
 * @see \Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts::addExtensionAttribute()
 */
class DiscountRule implements DiscountRuleInterface
{
    /**
     * @var DiscountDataInterface
     */
    private $discountData;

    /**
     * @var string
     */
    private $ruleLabel;

    /**
     * @var string
     */
    private $ruleId;

    /**
     * @var DiscountRuleExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param DiscountDataInterface $discountData
     * @param string $ruleLabel
     * @param string $ruleId
     * @param DiscountRuleExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        DiscountDataInterface $discountData,
        string $ruleLabel,
        string $ruleId,
        DiscountRuleExtensionInterface $extensionAttributes = null
    ) {
        $this->discountData = $discountData;
        $this->ruleLabel = $ruleLabel;
        $this->ruleId = $ruleId;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getDiscountData(): DiscountDataInterface
    {
        return $this->discountData;
    }

    /**
     * @inheritDoc
     */
    public function getRuleLabel(): string
    {
        return $this->ruleLabel;
    }

    /**
     * @inheritDoc
     */
    public function getRuleId(): string
    {
        return $this->ruleId;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?DiscountRuleExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
