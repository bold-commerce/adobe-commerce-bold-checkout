<?php
declare(strict_types=1);

namespace Bold\Platform\Model\ResourceModel\Quote\Item;

use Magento\Framework\App\ResourceConnection;

/**
 * Remove applied tax details data resource model.
 */
class DeleteTaxDetails
{
    private const TABLE = 'bold_checkout_quote_item_tax';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Remove applied taxes data in db for given quote item id.
     *
     * @param int $quoteItemId
     * @return void
     */
    public function delete(int $quoteItemId): void
    {
        $tableName = $this->connection->getConnection()->getTableName(self::TABLE);
        $this->connection->getConnection()->delete($tableName, ['item_id = ?' => $quoteItemId]);
    }
}
