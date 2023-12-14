<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion\Result;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data model representing module version information in API responses.
 *
 * This interface is designed to encapsulate data pertaining to a single module's version,
 * as part of the JSON response structure for the 'rest/V1/shops/:shopId/modules' endpoint
 * in the Bold Checkout API. It provides a standardized way to represent and access
 * essential details about the module, including its name and version.
 *
 * Key functionalities and data accessible through this interface include:
 *  - `getName()`: Retrieves the name of the module, which uniquely identifies it within the system.
 *  - `getVersion()`: Returns the version number of the module, indicating its release or build state.
 *  - `getExtensionAttributes()`: Allows access to additional, potentially future fields that might be
 *    included in the module version data, enhancing the API's capacity for future growth and customization.
 *
 * @see \Bold\Checkout\Api\Data\ModuleVersion\ResultInterface::getModules() for accessing multiple module versions.
 * @see \Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionExtensionInterface for additional extension attributes of module version data.
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
     * Retrieve module version extension attributes.
     *
     * Extension attributes are new, optional fields that can be added to existing
     * API data structures. This method provides a getter for these
     * additional fields in module version, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ModuleVersionExtensionInterface;
}
