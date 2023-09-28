<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Math\Random as MathRandom;

/**
 * Add comment to order in case it's quote total is different from bold order total.
 */
class AddOrderCommentQuoteTotalDiffBoldOrderTotalObserver implements ObserverInterface
{
    /**
     * @var MathRandom
     */
    private $mathRandom;

    /**
     * @param MathRandom $mathRandom
     */
    public function __construct(
        MathRandom $mathRandom
    ) {
        $this->mathRandom = $mathRandom;
    }

    /**
     * Add comment to order in case it's quote total is different from bold order total.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getDataByKey('order');
        $orderData = $observer->getDataByKey('orderData');
        $comments = $observer->getDataByKey('comments');

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

        $comments->setData($this->mathRandom->getRandomString(99), $comment);
    }
}
