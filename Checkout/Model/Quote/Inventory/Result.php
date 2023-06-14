<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Inventory;

use Bold\Checkout\Api\Data\Quote\Inventory\ResultInterface;

/**
 * Quote inventory result.
 */
class Result implements ResultInterface
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @var array
     */
    private $inventoryData;

    /**
     * @param array $inventoryData
     * @param array $errors
     */
    public function __construct(array $inventoryData = [], array $errors = [])
    {
        $this->errors = $errors;
        $this->inventoryData = $inventoryData;
    }

    /**
     * @inheritdoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritdoc
     */
    public function getInventoryData(): array
    {
        return $this->inventoryData;
    }
}
