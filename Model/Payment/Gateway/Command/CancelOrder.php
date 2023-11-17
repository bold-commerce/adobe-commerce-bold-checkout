<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

use Bold\Checkout\Model\Order\SetIsDelayedCapture;
use Bold\Checkout\Model\Payment\Gateway\Service;
use Exception;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Cancel bold order.
 */
class CancelOrder implements CommandInterface
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
    public function __construct(
        Service $gatewayService,
        SetIsDelayedCapture $setIsDelayedCapture
    ) {
        $this->gatewayService = $gatewayService;
        $this->setIsDelayedCapture = $setIsDelayedCapture;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    public function execute(array $commandSubject)
    {
        $paymentDataObject = $commandSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $this->setIsDelayedCapture->set($payment->getOrder());
        $this->gatewayService->cancel($payment->getOrder());
    }
}
