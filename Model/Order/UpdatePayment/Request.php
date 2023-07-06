<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\UpdatePayment;

use Bold\Checkout\Api\Data\Order\Payment\RequestExtensionInterface;
use Bold\Checkout\Api\Data\Order\Payment\RequestInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionInterface;

/**
 * Update payment request data model.
 */
class Request implements RequestInterface
{
    /**
     * @var OrderPaymentInterface
     */
    private $payment;

    /**
     * @var TransactionInterface
     */
    private $transaction;

    /**
     * @var RequestExtensionInterface
     */
    private $extensionAttributes = null;

    /**
     * @inheritDoc
     */
    public function setPayment(OrderPaymentInterface $payment): void
    {
        $this->payment = $payment;
    }

    /**
     * @inheritDoc
     */
    public function getPayment(): ?OrderPaymentInterface
    {
        return $this->payment;
    }

    /**
     * @inheritDoc
     */
    public function setTransaction(TransactionInterface $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @inheritDoc
     */
    public function getTransaction(): ?TransactionInterface
    {
        return $this->transaction;
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(RequestExtensionInterface $extensionAttributes): void
    {
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
