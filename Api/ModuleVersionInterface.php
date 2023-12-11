<?php
declare(strict_types=1);

namespace Bold\Checkout\Api;

/**
 * Get all installed modules and their versions.
 */
interface ModuleVersionInterface
{
    /**
     * Get all installed modules and their versions..
     *
     * @param string $shopId
     * @return \Bold\Checkout\Api\Data\ModuleVersion\ResultInterface
     */
    public function getModuleVersions(string $shopId): Data\ModuleVersion\ResultInterface;
}
