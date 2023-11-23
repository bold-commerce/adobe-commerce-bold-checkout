<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Data\ModuleVersion;

use Bold\Checkout\Api\Data\ModuleVersion\ModuleVersionInterface;
use Bold\Checkout\Api\Data\ModuleVersion\ResultInterface;

/**
 * @inheritDoc
 */
class Result implements ResultInterface
{
    /**
     * @var array
     */
    private $modules;

    /**
     * @param ModuleVersionInterface[] $modules
     */
    public function __construct(array $modules = [])
    {
        $this->modules = $modules;
    }

    /**
     * @inheritDoc
     */
    public function getModules(): array
    {
        return $this->modules;
    }
}
