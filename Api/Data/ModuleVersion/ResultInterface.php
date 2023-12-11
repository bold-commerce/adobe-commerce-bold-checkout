<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Bold Checkout modules and their versions result data interface.
 *
 * Represents a list of modules and their versions JSON response for the rest/V1/shops/:shopId/modules endpoint.
 * @see \Bold\Checkout\Api\ModuleVersionInterface::getModuleVersions()
 *
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Installed modules and their versions.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionInterface[]
     */
    public function getModules(): array;

    /**
     * Retrieve response extension attributes. Used in case additional fields are returned by the Bold API.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
