<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Bold order data resource model.
 */
class OrderExtensionData extends AbstractDb
{
    public const TABLE = 'bold_checkout_order';
    public const ORDER_ID = 'order_id';
    public const PUBLIC_ID = 'public_id';
    public const FINANCIAL_STATUS = 'financial_status';
    public const FULFILLMENT_STATUS = 'fulfillment_status';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, self::ORDER_ID);
    }
}
