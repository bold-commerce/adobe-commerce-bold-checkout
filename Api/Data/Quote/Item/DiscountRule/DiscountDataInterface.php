<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Quote\Item\DiscountRule;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Tests\NamingConvention\true\float;

/**
 * Quote Item Discount Data Interface.
 *
 * Used to store discount data for a quote item in bold_discounts extension attribute.
 * @see Bold/Checkout/etc/extension_attributes.xml
 *
 * @see \Bold\Checkout\Api\Data\Quote\Item\DiscountRuleInterface::getDiscountData()
 * @api
 */
interface DiscountDataInterface extends ExtensibleDataInterface
{
    /**
     * Get discount amount.
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Get base discount amount.
     *
     * @return float
     */
    public function getBaseAmount(): float;

    /**
     * Get discount original amount.
     *
     * @return float
     */
    public function getOriginalAmount(): float;

    /**
     * Get base discount original amount.
     *
     * @return float
     */
    public function getBaseOriginalAmount(): float;

    /**
     * Get discount data extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures in Magento. This method provides a getter for these
     * additional fields in discount data, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\Quote\Item\DiscountRule\DiscountDataExtensionInterface|null
     */
    public function getExtensionAttributes(): ?DiscountDataExtensionInterface;
}
