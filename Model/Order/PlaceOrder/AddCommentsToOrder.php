<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Add comments to order.
 */
class AddCommentsToOrder
{
    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var Order
     */
    private $orderResource;

    /**
     * @param EventManagerInterface $eventManager
     * @param Order $orderResource
     */
    public function __construct(
        EventManagerInterface $eventManager,
        Order $orderResource
    ) {
        $this->eventManager = $eventManager;
        $this->orderResource = $orderResource;
    }

    /**
     * Add comments to order.
     *
     * @param OrderInterface $order
     * @param OrderDataInterface $orderData
     * @return void
     */
    public function addComments(
        OrderInterface $order,
        OrderDataInterface $orderData
    ) {
        $comments = new DataObject([]);

        $this->eventManager->dispatch(
            'bold_checkout_add_comments_to_order_before',
            [
                'order' => $order,
                'orderData' => $orderData,
                'comments' => $comments,
            ]
        );

        if (empty($comments->getData())) {
            return;
        }

        foreach ($comments->getData() as $comment) {
            foreach ($order->getStatusHistories() as $history) {
                if ($history->getComment() === $comment->getText()) {
                    return;
                }
            }
            $order->addCommentToStatusHistory($comment);
        }

        $this->orderResource->save($order);
    }
}
