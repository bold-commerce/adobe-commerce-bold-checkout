<?php

declare(strict_types=1);

namespace Bold\Checkout\Setup\Patch\Data;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Perform Life elements upgrade.
 */
class AddLifeElementsValidationValuePatch implements DataPatchInterface
{
    private const PATH_LIFE_ELEMENTS = 'checkout/bold_checkout_life_elements/life_elements';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Json $serializer
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        Json                     $serializer
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Perform Life elements upgrade.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->moduleDataSetup->startSetup();

        $existingLifeElements = $this->getLifeElements();
        $updatedLifeElements = $this->updateLifeElements($existingLifeElements);
        $this->saveLifeElements($updatedLifeElements);

        $this->moduleDataSetup->endSetup();
    }

    /**
     * Get existing Life elements from database.
     *
     * @return array
     */
    private function getLifeElements(): array
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $connection->getTableName('core_config_data');
        $select = $connection->select()->from(
            $table,
            [
                'scope',
                'scope_id',
                'path',
                'value',
            ]
        )->where(
            'path = ?',
            self::PATH_LIFE_ELEMENTS
        );

        return $connection->fetchAll($select);
    }

    /**
     * Save updated Life elements to database.
     *
     * @param array $lifeElements
     * @return void
     */
    private function saveLifeElements(array $lifeElements): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $table = $connection->getTableName('core_config_data');
        $connection->insertOnDuplicate(
            $table,
            $lifeElements
        );
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Add to each element a 'input_regex' if it is absent.
     *
     * @param array $allLifeElements
     * @return array
     */
    private function updateLifeElements(array $allLifeElements): array
    {
        $result = [];
        foreach ($allLifeElements as $scopedLifeElement) {
            $updated = false;
            $lifeElements = $this->serializer->unserialize($scopedLifeElement['value']);
            foreach ($lifeElements as &$lifeElement) {
                if (!isset($lifeElement['input_regex'])) {
                    $lifeElement['input_regex'] = '';
                    $updated = true;
                }
            }
            if ($updated) {
                $scopedLifeElement['value'] = $this->serializer->serialize($lifeElements);
                $result[] = $scopedLifeElement;
            }
        }

        return $result;
    }
}
