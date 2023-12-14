<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Inventory;

use Bold\Checkout\Api\Data\Quote\Inventory\ResultExtensionInterface;
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
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param array $inventoryData
     * @param array $errors
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        array $inventoryData = [],
        array $errors = [],
        ?ResultExtensionInterface $extensionAttributes = null
    ) {
        $this->errors = $errors;
        $this->inventoryData = $inventoryData;
        $this->extensionAttributes = $extensionAttributes;
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

    /**
     * @inheirtDoc
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
