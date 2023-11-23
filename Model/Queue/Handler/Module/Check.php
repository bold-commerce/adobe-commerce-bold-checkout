<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Queue\Handler\Module;

use Bold\Checkout\Model\Config as ModuleConfig;
use Bold\Checkout\Model\ModuleInfo\Config;
use Bold\Checkout\Model\ModuleInfo\LatestModuleVersionUpdater;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerNameProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Notification\MessageInterface;
use Magento\Framework\Notification\NotifierInterface;

/**
 * Queue handler for checking module updates availability.
 */
class Check
{
    /**
     * @var LatestModuleVersionUpdater
     */
    private $latestModuleVersionUpdater;

    /**
     * @var ModuleComposerVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @var ModuleComposerNameProvider
     */
    private $moduleComposerNameProvider;

    /**
     * @var NotifierInterface
     */
    private $notifierPool;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @param LatestModuleVersionUpdater $latestModuleVersionUpdater
     * @param ModuleComposerVersionProvider $moduleVersionProvider
     * @param ModuleComposerNameProvider $moduleComposerNameProvider
     * @param NotifierInterface $notifierPool
     * @param Config $config
     */
    public function __construct(
        LatestModuleVersionUpdater    $latestModuleVersionUpdater,
        ModuleComposerVersionProvider $moduleVersionProvider,
        ModuleComposerNameProvider    $moduleComposerNameProvider,
        NotifierInterface             $notifierPool,
        Config                        $config,
        ModuleConfig                  $moduleConfig
    ) {
        $this->latestModuleVersionUpdater = $latestModuleVersionUpdater;
        $this->moduleVersionProvider = $moduleVersionProvider;
        $this->moduleComposerNameProvider = $moduleComposerNameProvider;
        $this->notifierPool = $notifierPool;
        $this->config = $config;
        $this->moduleConfig = $moduleConfig;
    }

    /**
     * Handle queue message.
     *
     * @param string[] $moduleNames
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $moduleNames): void
    {
        if (!$this->moduleConfig->isUpdatesCheckEnabled()) {
            return;
        }

        foreach ($moduleNames as $moduleName) {
            $lastCheckedVersion = $this->config->getLatestModuleVersion($moduleName);
            $this->latestModuleVersionUpdater->update($moduleName);
            $latestVersion = $this->config->getLatestModuleVersion($moduleName);
            $currentVersion = $this->moduleVersionProvider->getVersion($moduleName);
            $composerName = $this->moduleComposerNameProvider->getName($moduleName);
            if (
                version_compare($latestVersion, $currentVersion, '>') &&
                version_compare($latestVersion, $lastCheckedVersion, '>')
            ) {
                $this->notifierPool->add(
                    MessageInterface::SEVERITY_MAJOR,
                    __('Module "%1" can be updated.', $moduleName),
                    __('Please run command "composer require %1:%2" in server console.', $composerName, $latestVersion)
                );
            }
        }
    }
}
