<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

use Bold\Checkout\Model\Order\SetIsDelayedCapture;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Exception;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Capture order payment on bold side.
 */
class CapturePayment implements CommandInterface
{
    /**
     * @var Service
     */
    private $gatewayService;

    /**
     * @var SetIsDelayedCapture
     */
    private $setIsDelayedCapture;

    /**
     * @param Service $gatewayService
     * @param SetIsDelayedCapture $setIsDelayedCapture
     */
    public function __construct(Service $gatewayService, SetIsDelayedCapture $setIsDelayedCapture)
    {
        $this->gatewayService = $gatewayService;
        $this->setIsDelayedCapture = $setIsDelayedCapture;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function execute(array $commandSubject): void
    {
        $paymentDataObject = $commandSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();
        $this->setIsDelayedCapture->set($order);
        $amount = (float)$commandSubject['amount'];
        if ((float)$order->getGrandTotal() === $amount) {
            $payment->setTransactionId($this->gatewayService->captureFull($order))
                ->setShouldCloseParentTransaction(true);
            return;
        }
        $payment->setTransactionId($this->gatewayService->capturePartial($order, $amount));
        if ((float)$payment->getBaseAmountAuthorized() === $payment->getBaseAmountPaid() + $amount) {
            $payment->setShouldCloseParentTransaction(true);
        }
    }
}
