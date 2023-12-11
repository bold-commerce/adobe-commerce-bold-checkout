<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Data\ModuleVersion;

use Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionInterface;
use Bold\Checkout\Api\Data\ModuleVersion\ResultExtensionInterface;
use Bold\Checkout\Api\Data\ModuleVersion\ResultInterface;

/**
 * Bold Checkout modules and their versions result data model.
 *
 * Represents a list of modules and their versions JSON response for the rest/V1/shops/:shopId/modules endpoint.
 */
class Result implements ResultInterface
{
    /**
     * @var array
     */
    private $modules;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param ModuleVersionInterface[] $modules
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(array $modules = [], ResultExtensionInterface $extensionAttributes = null)
    {
        $this->modules = $modules;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
