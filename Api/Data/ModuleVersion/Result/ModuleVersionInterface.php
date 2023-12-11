<?php

declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion\Result;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Module version result data model.
 *
 * Represents a single module and its version JSON response for the rest/V1/shops/:shopId/modules endpoint.
 * @see \Bold\Checkout\Api\Data\ModuleVersion\ResultInterface::getModules()
 * @api
 */
interface ModuleVersionInterface extends ExtensibleDataInterface
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

    /**
     * Retrieve response extension attributes. Used in case additional fields are returned by the Bold API.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ModuleVersionExtensionInterface;
}
