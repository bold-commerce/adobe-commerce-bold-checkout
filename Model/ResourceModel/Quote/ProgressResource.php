<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;

/**
 * Quote progress resource model.
 */
class ProgressResource
{
    private const TABLE = 'bold_checkout_quote_progress';

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
     * Check if quote in progress.
     *
     * @param int $quoteId
     * @return bool
     */
    public function getIsInProgress(int $quoteId): bool
    {
        $sql = $this->connection->getConnection()->select()->from(
            $this->connection->getTableName(self::TABLE)
        )->where('quote_id = (?)', $quoteId);

        return $this->connection->getConnection()->fetchOne($sql) !== false;
    }

    /**
     * Create quote progress.
     *
     * @param int $quoteId
     * @return void
     */
    public function create(int $quoteId): void
    {
        $this->connection->getConnection()->insertOnDuplicate(
            $this->connection->getTableName(self::TABLE),
            ['quote_id' => $quoteId],
            ['quote_id']
        );
    }

    /**
     * Delete quote progress.
     *
     * @param int $quoteId
     * @return void
     */
    public function delete(int $quoteId): void
    {
        $this->connection->getConnection()->delete(
            $this->connection->getTableName(self::TABLE),
            ['quote_id', $quoteId]
        );
    }
}
