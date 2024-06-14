<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

/**
 * Get all installed Bold modules.
 */
class InstalledModulesProvider
{
    /**
     * @var array
     */
    private $moduleList;

    /**
     * @param array $moduleList
     */
    public function __construct(
        array $moduleList = []
    ) {
        $this->moduleList = $moduleList;
    }

    /**
     * Get installed modules list.
     *
     * @return string[]
     */
    public function getModuleList(): array
    {
        return $this->moduleList;
    }
}
