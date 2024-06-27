<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Data;

use Bold\Checkout\Api\Data\DiscountDataInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\ExtensionAttributesInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Discount Data Model
 */
class DiscountData extends AbstractExtensibleModel implements DiscountDataInterface
{
    const AMOUNT = 'amount';
    const BASE_AMOUNT = 'base_amount';
    const ORIGINAL_AMOUNT = 'original_amount';
    const BASE_ORIGINAL_AMOUNT = 'base_original_amount';

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return (float)$this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getBaseAmount(): float
    {
        return (float)$this->getData(self::BASE_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getOriginalAmount(): float
    {
        return (float)$this->getData(self::ORIGINAL_AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function getBaseOriginalAmount(): float
    {
        return (float)$this->getData(self::BASE_ORIGINAL_AMOUNT);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return ExtensionAttributesInterface|null
     */
    public function getExtensionAttributes(): ?ExtensionAttributesInterface
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
    ): DiscountData {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
