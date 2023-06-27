<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Inventory\Result;

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
     * @var float
     */
    private $salableQty;

    /**
     * @var bool
     */
    private $isSalable;

    /**
     * @param int $cartItemId
     * @param float $salableQty
     * @param bool $isSalable
     */
    public function __construct(int $cartItemId, float $salableQty, bool $isSalable)
    {
        $this->cartItemId = $cartItemId;
        $this->salableQty = $salableQty;
        $this->isSalable = $isSalable;
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
     * @inheritdoc
     */
    public function getSalableQty(): float
    {
        return $this->salableQty;
    }
}
