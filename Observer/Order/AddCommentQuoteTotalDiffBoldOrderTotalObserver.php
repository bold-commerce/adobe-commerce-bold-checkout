<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add comment to order in case it's quote total is different from bold order total.
 */
class AddCommentQuoteTotalDiffBoldOrderTotalObserver implements ObserverInterface
{
    /**
     * Add comment to order in case it's quote total is different from bold order total.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $orderData = $observer->getOrderData();
        $comments = $observer->getComments();

        if ($order->getBaseGrandTotal() - $orderData->getTotal() !== 0) {
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

        $comments->setData($comment);
    }
}
