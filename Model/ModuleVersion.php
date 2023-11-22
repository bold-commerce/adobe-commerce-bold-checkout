<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\Data\ModuleVersion\ModuleVersionInterfaceFactory;
use Bold\Checkout\Api\Data\ModuleVersion\ResultInterface;
use Bold\Checkout\Api\Data\ModuleVersion\ResultInterfaceFactory;
use Bold\Checkout\Api\ModuleVersionInterface;
use Bold\Checkout\Model\ModuleInfo\InstalledModulesProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerNameProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;

/**
 * @inheritDoc
 */
class ModuleVersion implements ModuleVersionInterface
{
    /**
     * @var InstalledModulesProvider
     */
    private $installedModulesProvider;

    /**
     * @var ModuleComposerVersionProvider
     */
    private $composerVersionProvider;

    /**
     * @var ModuleComposerNameProvider
     */
    private $composerNameProvider;

    /**
     * @var ResultInterfaceFactory
     */
    private $resultFactory;

    /**
     * @var ModuleVersionInterfaceFactory
     */
    private $moduleVersionFactory;

    /**
     * @param InstalledModulesProvider $installedModulesProvider
     * @param ModuleComposerVersionProvider $composerVersionProvider
     * @param ModuleComposerNameProvider $composerNameProvider
     * @param ResultInterfaceFactory $resultFactory
     * @param ModuleVersionInterfaceFactory $moduleVersionFactory
     */
    public function __construct(
        InstalledModulesProvider      $installedModulesProvider,
        ModuleComposerVersionProvider $composerVersionProvider,
        ModuleComposerNameProvider    $composerNameProvider,
        ResultInterfaceFactory        $resultFactory,
        ModuleVersionInterfaceFactory $moduleVersionFactory
    ) {
        $this->installedModulesProvider = $installedModulesProvider;
        $this->composerVersionProvider = $composerVersionProvider;
        $this->composerNameProvider = $composerNameProvider;
        $this->resultFactory = $resultFactory;
        $this->moduleVersionFactory = $moduleVersionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getModuleVersions(string $shopId): ResultInterface
    {
        return $this->resultFactory->create(
            [
                'modules' => array_map(
                    function (string $moduleName) {
                        return $this->moduleVersionFactory->create(
                            [
                                'name' => $this->composerNameProvider->getName($moduleName),
                                'version' => $this->composerVersionProvider->getVersion($moduleName)
                            ]
                        );
                    },
                    $this->installedModulesProvider->getModuleList()
                ),
            ]
        );
    }
}
