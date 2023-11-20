<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Get composer latest module version.
 */
class Config
{
    private const PATH_MODULE_VERSION = 'checkout/bold_checkout/latest_version_%s';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $writer
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writer,
        TypeListInterface $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Get composer latest module version.
     *
     * @param string $module
     * @return string
     */
    public function getLatestModuleVersion(string $module): string
    {
        return $this->scopeConfig->getValue($this->getModuleVersionPath($module)) ?? '0.0.0';
    }

    /**
     * Set composer latest module version.
     *
     * @param string $module
     * @param string $version
     */
    public function setLatestModuleVersion(string $module, string $version): void
    {
        $this->writer->save($this->getModuleVersionPath($module), $version);
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }

    /**
     * Generate config path for module version data.
     *
     * @param string $module
     * @return string
     */
    private function getModuleVersionPath(string $module): string
    {
        return sprintf(self::PATH_MODULE_VERSION, strtolower($module));
    }
}
