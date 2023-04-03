<?php
declare(strict_types=1);

namespace Bold\Platform\Model\ResourceModel\Quote\Item;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Save applied tax details data resource model.
 */
class SaveTaxDetails
{
    private const TABLE = 'bold_checkout_quote_item_tax';

    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param ResourceConnection $connection
     * @param Json $json
     */
    public function __construct(
        ResourceConnection $connection,
        Json $json
    ) {
        $this->connection = $connection;
        $this->json = $json;
    }

    /**
     * Save serialized applied taxes data for given quote item id in db.
     *
     * @param int $quoteItemId
     * @param array $taxDetails
     * @return void
     */
    public function save(int $quoteItemId, array $taxDetails): void
    {
        $tableName = $this->connection->getConnection()->getTableName(self::TABLE);
        $data = [
            'item_id' => $quoteItemId,
            'tax_details' => $this->json->serialize($taxDetails),
        ];
        $this->connection->getConnection()->insertOnDuplicate($tableName, $data);
    }
}
