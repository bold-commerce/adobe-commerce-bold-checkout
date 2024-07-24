<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Http\Client;

use Bold\Checkout\Model\ModuleInfo\InstalledModulesProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Get system info headers.
 */
class SystemInfoHeaders
{
    private const HEADER_PREFIX_MAGENTO_EDITION = 'Magento-Edition';
    private const HEADER_PREFIX_MAGENTO_VERSION = 'Magento-Version';
    private const HEADER_PREFIX_PHP_VERSION = 'PHP-Version';
    private const HEADER_PREFIX_MODULE_VERSION = 'Module-Version';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var ModuleComposerVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @var InstalledModulesProvider
     */
    private $installedModulesProvider;

    /**
     * @param ProductMetadataInterface $productMetadata
     * @param ModuleComposerVersionProvider $moduleVersionProvider
     * @param InstalledModulesProvider $installedModulesProvider
     */
    public function __construct(
        ProductMetadataInterface      $productMetadata,
        ModuleComposerVersionProvider $moduleVersionProvider,
        InstalledModulesProvider      $installedModulesProvider
    )
    {
        $this->productMetadata = $productMetadata;
        $this->moduleVersionProvider = $moduleVersionProvider;
        $this->installedModulesProvider = $installedModulesProvider;
    }

    /**
     * Get system info headers.
     *
     * @return array
     */
    public function getData(): array
    {
        return [
            self::HEADER_PREFIX_MAGENTO_EDITION => $this->productMetadata->getEdition(),
            self::HEADER_PREFIX_MAGENTO_VERSION => $this->productMetadata->getVersion(),
            self::HEADER_PREFIX_PHP_VERSION => phpversion(),
            self::HEADER_PREFIX_MODULE_VERSION => $this->getModuleData(),
        ];
    }

    /**
     * Get installed modules versions.
     *
     * @return string
     */
    private function getModuleData(): string
    {
        $result = [];
        foreach ($this->installedModulesProvider->getModuleList() as $module) {
            $version = $this->moduleVersionProvider->getVersion($module);
            $result[] = sprintf('%s %s', $module, $version);
        }

        return join('; ', $result);
    }
}
