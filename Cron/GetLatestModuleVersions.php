<?php

namespace Bold\Checkout\Cron;

use Bold\Checkout\Model\Config;
use Bold\Checkout\Model\ModuleInfo\InstalledModulesProvider;
use Magento\Framework\MessageQueue\PublisherInterface;

/**
 * Cron job to check module updates availability.
 */
class GetLatestModuleVersions
{
    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var InstalledModulesProvider
     */
    private $installedModulesProvider;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param InstalledModulesProvider $installedModulesProvider
     * @param PublisherInterface $publisher
     * @param Config $config
     */
    public function __construct(
        InstalledModulesProvider $installedModulesProvider,
        PublisherInterface       $publisher,
        Config                   $config
    ) {
        $this->installedModulesProvider = $installedModulesProvider;
        $this->publisher = $publisher;
        $this->config = $config;
    }

    /**
     * Add a queue job.
     *
     * @return void
     */
    public function execute(): void
    {
        if ($this->config->isUpdatesCheckEnabled()) {
            $moduleList = array_unique(array_values($this->installedModulesProvider->getModuleList()));
            $this->publisher->publish('bold.checkout.module.check', $moduleList);
        }
    }
}
