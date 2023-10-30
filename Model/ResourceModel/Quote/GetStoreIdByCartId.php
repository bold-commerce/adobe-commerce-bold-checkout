<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;

/**
 * Get store id by cart id resource model.
 */
class GetStoreIdByCartId
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Retrieve store id by cart id.
     *
     * @param int $cartId
     * @return int
     */
    public function getStoreId(int $cartId): int
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('quote'),
            ['store_id']
        )->where('entity_id = (?)', $cartId);
        return (int)$this->resourceConnection->getConnection()->fetchOne($select);
    }
}
