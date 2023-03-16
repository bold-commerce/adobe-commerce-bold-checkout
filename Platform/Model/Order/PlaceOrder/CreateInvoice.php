<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Order\PlaceOrder;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Create offline invoice for order service.
 */
class CreateInvoice
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
     * Create order invoice in case payment has been captured on bold checkout.
     *
     * @param OrderInterface $order
     * @return void
     * @throws AlreadyExistsException
     * @throws LocalizedException
     */
    public function create(OrderInterface $order)
    {
        $payment = $order->getPayment();
        if (!$payment->getBaseAmountPaid()) {
            return;
        }
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->setEmailSent(true);
        $invoice->setTransactionId($payment->getLastTransId());
        $invoice->getOrder()->setCustomerNoteNotify(true);
        $invoice->getOrder()->setIsInProcess(true);
        $order->addRelatedObject($invoice);
        $this->orderResource->save($order);
    }
}
