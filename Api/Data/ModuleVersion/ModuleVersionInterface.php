<?php

declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion;

/**
 * Installed module and it's version.
 */
interface ModuleVersionInterface
{
    /**
     * Get module name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get module version.
     *
     * @return string
     */
    public function getVersion(): string;
}
