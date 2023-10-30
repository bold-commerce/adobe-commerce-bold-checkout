<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel\Quote;

use Magento\Framework\App\ResourceConnection;

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

    public function getStoreId(int $cartId): int
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('quote'),
            ['store_id']
        )->where('entity_id = (?)', $cartId);
        return (int)$this->resourceConnection->getConnection()->fetchOne($select);
    }
}
