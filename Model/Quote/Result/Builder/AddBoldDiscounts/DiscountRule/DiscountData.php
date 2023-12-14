<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts\DiscountRule;

use Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataExtensionInterface;
use Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataInterface;

/**
 * Quote item discount data model. Used to add 'bold_discounts' extension attribute to quote item.
 *
 * @see \Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts\DiscountRule::getDiscountData()
 */
class DiscountData implements DiscountDataInterface
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $baseAmount;

    /**
     * @var float
     */
    private $originalAmount;

    /**
     * @var float
     */
    private $baseOriginalAmount;

    /**
     * @var DiscountDataExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param float $amount
     * @param float $baseAmount
     * @param float $originalAmount
     * @param float $baseOriginalAmount
     * @param DiscountDataExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        float $amount,
        float $baseAmount,
        float $originalAmount,
        float $baseOriginalAmount,
        DiscountDataExtensionInterface $extensionAttributes = null
    ) {
        $this->amount = $amount;
        $this->baseAmount = $baseAmount;
        $this->originalAmount = $originalAmount;
        $this->baseOriginalAmount = $baseOriginalAmount;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @inheritDoc
     */
    public function getBaseAmount(): float
    {
        return $this->baseAmount;
    }

    /**
     * @inheritDoc
     */
    public function getOriginalAmount(): float
    {
        return $this->originalAmount;
    }

    /**
     * @inheritDoc
     */
    public function getBaseOriginalAmount(): float
    {
        return $this->baseOriginalAmount;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?DiscountDataExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
