<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Data;

use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Bold\Checkout\Api\Data\RuleDiscountInterface;

/**
 * Data Model for Rule Discount
 */
class RuleDiscount extends AbstractExtensibleModel implements RuleDiscountInterface
{
    const KEY_DISCOUNT_DATA = 'discount';
    const KEY_RULE_LABEL = 'rule';
    const KEY_RULE_ID = 'rule_id';

    /**
     * @inheritDoc
     */
    public function getDiscountData()
    {
        return $this->getData(self::KEY_DISCOUNT_DATA);
    }

    /**
     * @inheritDoc
     */
    public function getRuleLabel()
    {
        return $this->getData(self::KEY_RULE_LABEL);
    }

    /**
     * @inheritDoc
     */
    public function getRuleID()
    {
        return $this->getData(self::KEY_RULE_ID);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     *
     * @param ExtensionAttributesInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        ExtensionAttributesInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
