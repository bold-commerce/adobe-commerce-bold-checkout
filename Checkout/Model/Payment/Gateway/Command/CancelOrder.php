<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Command;

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
    public function execute(array $commandSubject)
    {
        $payment = $commandSubject['payment'];
        $this->gatewayService->cancel($payment->getOrder());
    }
}
