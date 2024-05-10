<?php

declare(strict_types=1);

namespace Bold\Checkout\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Perform Life elements upgrade - fix.
 */
class AddLifeElementsValidationValueFixPatch implements DataPatchInterface
{
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
        return [AddLifeElementsValidationValuePatch::class];
    }

    /**
     * Perform Life elements upgrade - fix.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $connection->delete(
            $connection->getTableName('core_config_data'),
            [
                'path = ?' => 'general',
            ]
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }
}
