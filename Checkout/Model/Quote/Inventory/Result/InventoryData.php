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
     * @param int $cartItemId
     * @param float $salableQty
     */
    public function __construct(int $cartItemId, float $salableQty)
    {
        $this->cartItemId = $cartItemId;
        $this->salableQty = $salableQty;
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
        return $this->salableQty > 0;
    }

    /**
     * @inheritdoc
     */
    public function getSalableQty(): float
    {
        return $this->salableQty;
    }
}
