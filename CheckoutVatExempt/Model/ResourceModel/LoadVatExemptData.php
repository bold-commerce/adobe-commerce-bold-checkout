<?php
declare(strict_types=1);

namespace Bold\CheckoutVatExempt\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Load vat exempt data from database resource model.
 */
class LoadVatExemptData
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Load vat exempt data from database
     *
     * @param int $quoteId
     * @return array
     */
    public function load(int $quoteId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('bold_checkout_vat_exempt');

        $select = $connection->select()
            ->from($tableName)
            ->where('quote_id = ?', $quoteId);

        return $connection->fetchRow($select) ?: [];
    }
}
