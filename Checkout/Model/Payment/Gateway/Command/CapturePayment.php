<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

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
        $payment = $commandSubject['payment'];
        $order = $payment->getOrder();
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
