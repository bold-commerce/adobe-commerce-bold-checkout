<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Mass delete config values.
 */
class ConfigRemover extends AbstractDb
{
    /**
     * Mass delete config values.
     *
     * Removes records from core_config_data table using 'LIKE' expression.
     *
     * @param string $path
     * @param string $scope
     * @param int $scopeId
     * @throws LocalizedException
     */
    public function deleteConfig(string $path, string $scope, int $scopeId): void
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getMainTable(),
            [
                $connection->quoteInto('path LIKE ?', $path),
                $connection->quoteInto('scope = ?', $scope),
                $connection->quoteInto('scope_id = ?', $scopeId)
            ]
        );
    }

    /**
     * Define main table.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }
}
