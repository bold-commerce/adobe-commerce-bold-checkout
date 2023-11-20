<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Shell;

/**
 * Get composer latest module version.
 */
class LatestModuleVersionUpdater
{
    private const COMMAND_TEMPLATE = 'composer show "%s" --latest --all --working-dir="%s"';
    private const LATEST_PATTERN = '/latest\s+:\s+([\d.]+)/';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var ModuleComposerNameProvider
     */
    private $composerNameProvider;

    /**
     * @param Config $config
     * @param Shell $shell
     * @param DirectoryList $directoryList
     * @param ModuleComposerNameProvider $composerNameProvider
     */
    public function __construct(
        Config                     $config,
        Shell                      $shell,
        DirectoryList              $directoryList,
        ModuleComposerNameProvider $composerNameProvider
    ) {
        $this->config = $config;
        $this->shell = $shell;
        $this->directoryList = $directoryList;
        $this->composerNameProvider = $composerNameProvider;
    }

    /**
     * Update module latest version data.
     *
     * @param string $moduleName
     * @return void
     * @throws LocalizedException
     */
    public function update(string $moduleName): string
    {
        $latestVersion = $this->getLatestModuleVersion($moduleName);
        if ($latestVersion) {
            $this->config->setLatestModuleVersion($moduleName, $latestVersion);
        }

        return $latestVersion;
    }

    /**
     * Get module latest version data.
     *
     * @param string $moduleName
     * @return string|null
     * @throws LocalizedException
     */
    private function getLatestModuleVersion(string $moduleName): ?string
    {
        $moduleName = $this->composerNameProvider->getName($moduleName);
        $rootDirectory = $this->directoryList->getRoot();
        $command = sprintf(self::COMMAND_TEMPLATE, $moduleName, $rootDirectory);
        $result = $this->shell->execute($command);

        return preg_match(self::LATEST_PATTERN, $result, $matches)
            ? $matches[1]
            : null;
    }
}
