<?php

declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion;

/**
 * Installed modules and their versions.
 */
interface ResultInterface
{
    /**
     * Installed modules and their versions.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\ModuleVersionInterface[]
     */
    public function getModules(): array;
}
