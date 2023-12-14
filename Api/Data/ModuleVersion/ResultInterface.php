<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\ModuleVersion;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for the data model representing a collection of installed Bold Checkout modules
 * along with their respective versions. This interface is pivotal in structuring the JSON response
 * for the 'rest/V1/shops/:shopId/modules' endpoint in the Bold Checkout API, providing a consolidated
 * view of the module versions currently operational within the Bold Checkout environment.
 *
 * This interface primarily offers the following functionality:
 *  - `getModules()`: Returns an array of ModuleVersionInterface objects, each representing a single
 *    module's version information. This method is central to obtaining a comprehensive snapshot of the
 *    installed Bold modules along with their respective version numbers.
 *  - `getExtensionAttributes()`: Facilitates access to additional extension fields that might be added
 *    to the module versions result data in future iterations of the API, ensuring the interface remains
 *    adaptable and extensible for future developments and custom needs.
 *
 * Adhering to Magento's ExtensibleDataInterface, this interface ensures compatibility and flexibility
 * within Magento's framework. It allows for an extensible approach to representing data, which is
 * particularly useful in accommodating evolving API structures and diverse module configurations.
 *
 * @see \Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionInterface for details on individual module version data.
 * @see \Bold\Checkout\Api\Data\ModuleVersion\ResultExtensionInterface for potential future extension attributes of the response.
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
     * Retrieve response extension attributes.
     *
     *  Extension attributes are new, optional fields that can be added to existing
     *  API data structures. This method provides a getter for these
     *  additional fields in module versions result, allowing for future extensions and customizations.
     *
     * @return \Bold\Checkout\Api\Data\ModuleVersion\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
