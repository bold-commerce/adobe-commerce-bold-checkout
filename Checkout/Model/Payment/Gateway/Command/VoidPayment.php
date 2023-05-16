<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

use Bold\Checkout\Model\Payment\Gateway\Service;
use Exception;
use Magento\Payment\Gateway\CommandInterface;

/**
 * Void bold order payment.
 */
class VoidPayment implements CommandInterface
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
        $paymentDataObject = $commandSubject['payment'];
        $payment = $paymentDataObject->getPayment();
        $this->gatewayService->cancel($payment->getOrder(), Service::VOID);
    }
}
