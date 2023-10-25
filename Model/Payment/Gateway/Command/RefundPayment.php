<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

use Bold\Checkout\Model\Payment\Gateway\Service;
use Exception;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Data\PaymentDataObject;

/**
 * Refund bold order payment.
 */
class RefundPayment implements CommandInterface
{
    /**
     * @var Service
     */
    private $gatewayService;

    /**
     * @param Service $gatewayService
     */
    public function __construct(Service $gatewayService)
    {
        $this->gatewayService = $gatewayService;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function execute(array $commandSubject): void
    {
        /** @var PaymentDataObject */
        $paymentDataObject = $commandSubject['payment'];
        $amount = (float)$commandSubject['amount'];

        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();
        if ((float)$order->getGrandTotal() <= $amount) {
            $transactionId = $this->gatewayService->refundFull($order);
            $payment->setTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction(true);

            return;
        }
        $transactionId = $this->gatewayService->refundPartial($order, $amount);
        $payment->setTransactionId($transactionId)->setIsTransactionClosed(1);
        if ((float)$payment->getBaseAmountPaid() === $payment->getBaseAmountRefunded() + $amount) {
            $payment->setShouldCloseParentTransaction(true);
        }
    }
}
