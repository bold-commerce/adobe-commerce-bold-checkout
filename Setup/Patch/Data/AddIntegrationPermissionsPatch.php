<?php

declare(strict_types=1);

namespace Bold\Checkout\Setup\Patch\Data;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Perform integration permissions upgrade.
 */
class AddIntegrationPermissionsPatch implements DataPatchInterface
{
    private const RESOURCE = 'Bold_Checkout::integration';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Perform integration permissions upgrade.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('authorization_rule'),
            [
                'resource_id = ?' => self::RESOURCE,
                'role_id IN (?)' => $this->getRoleIdsToDelete(),
            ]
        );
        $this->moduleDataSetup->getConnection()->insertMultiple(
            $this->moduleDataSetup->getTable('authorization_rule'),
            $this->getNewAuthorizationRules()
        );

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get role IDs to delete.
     *
     * @return array
     */
    private function getRoleIdsToDelete(): array
    {
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from(['ar' => $this->moduleDataSetup->getTable('authorization_role')], 'role_id')
            ->join(
                ['i' => $this->moduleDataSetup->getTable('integration')],
                'i.integration_id = ar.user_id',
                []
            )
            ->where('ar.user_type = ?', UserContextInterface::USER_TYPE_INTEGRATION)
            ->where('i.name LIKE ?', 'BoldPlatformIntegration%');

        return $this->moduleDataSetup->getConnection()->fetchCol($select);
    }

    /**
     * Get new authorization rules data.
     *
     * @return array
     */
    private function getNewAuthorizationRules(): array
    {
        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from(['ar' => $this->moduleDataSetup->getTable('authorization_role')], ['role_id'])
            ->join(
                ['i' => $this->moduleDataSetup->getTable('integration')],
                'i.integration_id = ar.user_id',
                []
            )
            ->where('ar.user_type = ?', UserContextInterface::USER_TYPE_INTEGRATION)
            ->where('i.name LIKE ?', 'BoldPlatformIntegration%');
        $roleIds = $this->moduleDataSetup->getConnection()->fetchCol($select);
        $data = [];
        foreach ($roleIds as $roleId) {
            $data[] = [
                'role_id' => $roleId,
                'resource_id' => self::RESOURCE,
                'permission' => 'allow',
            ];
        }

        return $data;
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
    public static function getDependencies()
    {
        return [];
    }
}
