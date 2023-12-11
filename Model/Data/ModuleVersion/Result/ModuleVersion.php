<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Data\ModuleVersion\Result;

use Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionExtensionInterface;
use Bold\Checkout\Api\Data\ModuleVersion\Result\ModuleVersionInterface;

/**
 * Single module and its version result data model used in response for the rest/V1/shops/:shopId/modules endpoint.
 */
class ModuleVersion implements ModuleVersionInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $version;

    /**
     * @var ModuleVersionExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param string $name
     * @param string $version
     * @param ModuleVersionExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        string $name = '',
        string $version = '',
        ModuleVersionExtensionInterface $extensionAttributes = null
    ) {
        $this->name = $name;
        $this->version = $version;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ModuleVersionExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
