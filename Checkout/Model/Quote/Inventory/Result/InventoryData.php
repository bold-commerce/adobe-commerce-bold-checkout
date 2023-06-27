<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Inventory\Result;

use Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataExtensionInterface;
use Bold\Checkout\Api\Data\Quote\Inventory\Result\InventoryDataInterface;

/**
 * Quote item inventory data.
 */
class InventoryData implements InventoryDataInterface
{
    /**
     * @var int
     */
    private $cartItemId;

    /**
     * @var bool
     */
    private $isSalable;

    /**
     * @var InventoryDataExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param int $cartItemId
     * @param bool $isSalable
     * @param InventoryDataExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        int $cartItemId,
        bool $isSalable,
        InventoryDataExtensionInterface $extensionAttributes = null
    ) {
        $this->cartItemId = $cartItemId;
        $this->isSalable = $isSalable;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritdoc
     */
    public function getCartItemId(): int
    {
        return $this->cartItemId;
    }

    /**
     * @inheritdoc
     */
    public function isSalable(): bool
    {
        return $this->isSalable;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(InventoryDataExtensionInterface $extensionAttributes): void {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?InventoryDataExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
