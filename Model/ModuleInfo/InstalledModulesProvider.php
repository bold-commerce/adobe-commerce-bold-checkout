<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ModuleInfo;

/**
 * Get all installed Bold modules.
 */
class InstalledModulesProvider
{
    private const PAYPAL_FLOW_MODULE = 'Bold_CheckoutFlowPaypal';
    
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

    /**
     * Get if Paypal Flow module is installed
     * 
     * @return bool
     */
    public function isPayPalFlowInstalled(): bool
    {
        return array_contains($this->moduleList, self::PAYPAL_FLOW_MODULE);
    }
}
