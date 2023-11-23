<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Bold order data resource model.
 */
class OrderExtensionData extends AbstractDb
{
    public const TABLE = 'bold_checkout_order';
    public const ID = 'id';
    public const ORDER_ID = 'order_id';
    public const PUBLIC_ID = 'public_id';
    public const IS_DELAYED_CAPTURE = 'is_delayed_capture';

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, self::ID);
    }
}
