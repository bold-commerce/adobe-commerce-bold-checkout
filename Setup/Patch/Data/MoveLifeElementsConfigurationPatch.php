<?php

declare(strict_types=1);

namespace Bold\Checkout\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Update Life element configuration path.
 */
class MoveLifeElementsConfigurationPatch implements DataPatchInterface
{
    private const SOURCE = 'checkout/bold_checkout_life_elements/life_elements';
    private const TARGET = 'checkout/bold_checkout_custom_elements/life_elements';
    private const PATH = 'path';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $table = $this->moduleDataSetup->getTable('core_config_data');
        $this->moduleDataSetup->updateTableRow(
            $table,
            self::PATH,
            self::SOURCE,
            self::PATH,
            self::TARGET
        );

        $this->moduleDataSetup->endSetup();
    }
}
