<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Model\ResourceModel\ConfigRemover;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Delete configuration values.
 */
class ClearModuleConfiguration
{
    /**
     * @var ConfigRemover
     */
    private $configRemover;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var array
     */
    private $paths;

    /**
     * @param ConfigRemover $configRemover
     * @param ScopeConfigInterface $scopeConfig
     * @param TypeListInterface $cacheTypeList
     * @param array $paths
     */
    public function __construct(
        ConfigRemover        $configRemover,
        ScopeConfigInterface $scopeConfig,
        TypeListInterface    $cacheTypeList,
        array                $paths = []
    ) {
        $this->configRemover = $configRemover;
        $this->scopeConfig = $scopeConfig;
        $this->cacheTypeList = $cacheTypeList;
        $this->paths = $paths;
    }

    /**
     * Mass delete config values.
     *
     * Removes records for specific scope by paths, provided through dependency injection,
     * and clears configuration cache.
     *
     * @throws LocalizedException
     */
    public function clear(int $websiteId): void
    {
        foreach ($this->paths as $path) {
            $this->configRemover->deleteConfig(
                $path,
                $websiteId ? ScopeInterface::SCOPE_WEBSITES : ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $websiteId
            );
        }

        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }
}
