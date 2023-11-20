<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

use Exception;

/**
 * Get composer module version.
 */
class LatestModuleVersionProvider
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Get composer module version.
     *
     * @param string $module
     * @return string
     */
    public function getVersion(string $module): string
    {
        try {
            $version = $this->config->getLatestModuleVersion($module);
        } catch (Exception $e) {
            $version = (string)__('Error reading module version.');
        }

        return $version;
    }
}
