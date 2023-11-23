<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Data\ModuleVersion;

use Bold\Checkout\Api\Data\ModuleVersion\ModuleVersionInterface;

/**
 * @inheritDoc
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
     * @param string $name
     * @param string $version
     */
    public function __construct(string $name = '', string $version = '')
    {
        $this->name = $name;
        $this->version = $version;
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
}
