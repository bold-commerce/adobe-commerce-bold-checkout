<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\Item;

use Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Discount rule data interface.
 *
 * Used to store discount rule data as extension attribute in quote item.
 * @see Bold/Checkout/etc/extension_attributes.xml
 *
 * @see \Bold\Checkout\Model\Quote\Result\Builder\AddBoldDiscounts::addExtensionAttribute()
 * @api
 */
interface DiscountRuleInterface extends ExtensibleDataInterface
{
    /**
     * Get Discount Data
     *
     * @return \Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataInterface
     */
    public function getDiscountData(): DiscountDataInterface;

    /**
     * Get Rule Label
     *
     * @return string
     */
    public function getRuleLabel(): string;

    /**
     * Get applied rules id.
     *
     * @return int
     */
    public function getRuleId(): string;

    /**
     * Get discount rule data extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures in Magento. This method provides a getter for these
     * additional fields in discount rule data, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Item\DiscountRuleExtensionInterface|null
     */
    public function getExtensionAttributes(): ?DiscountRuleExtensionInterface;
}
