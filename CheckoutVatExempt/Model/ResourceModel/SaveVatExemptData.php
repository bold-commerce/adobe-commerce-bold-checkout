<?php
declare(strict_types=1);

namespace Bold\CheckoutVatExempt\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

/**
 * Save vat exempt data to database resource model.
 */
class SaveVatExemptData
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
     * Persist vat exempt data to database
     *
     * @param array $vatExemptData
     * @return void
     */
    public function save(array $vatExemptData): void
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('bold_checkout_vat_exempt');

        $connection->insertOnDuplicate($tableName, $vatExemptData);
    }
}
