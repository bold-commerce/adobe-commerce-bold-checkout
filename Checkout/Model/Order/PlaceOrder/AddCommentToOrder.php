<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Add comment to order in case it's quote total is different from bold order total.
 */
class AddCommentToOrder
{
    /**
     * @var Order
     */
    private $orderResource;

    /**
     * @param Order $orderResource
     */
    public function __construct(Order $orderResource)
    {
        $this->orderResource = $orderResource;
    }

    /**
     * Add comment to order in case it's quote total is different from bold order total.
     *
     * @param OrderInterface $order
     * @param OrderDataInterface $orderData
     * @return void
     * @throws Exception
     */
    public function addComment(
        OrderInterface $order,
        OrderDataInterface $orderData
    ) {
        if ($order->getBaseGrandTotal() === $orderData->getTotal()) {
            return;
        }
        $operation = $order->hasInvoices() ? 'refund' : 'cancel';
        $transactionType = $order->hasInvoices() ? 'payment' : 'authorization';
        $comment = __(
            'Please consider to %1 this order due to it\'s total = %2 mismatch %3 transaction amount = %4. '
            . 'For more details please refer to Bold Help Center at "https://support.boldcommerce.com"',
            $operation,
            $order->getBaseGrandTotal(),
            $transactionType,
            $orderData->getTotal()
        );
        $order->addCommentToStatusHistory($comment);
        $this->orderResource->save($order);
    }
}
