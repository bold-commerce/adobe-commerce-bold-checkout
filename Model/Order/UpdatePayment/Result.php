<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\UpdatePayment;

use Bold\Checkout\Api\Data\Order\Payment\ResultExtensionInterface;
use Bold\Checkout\Api\Data\Order\Payment\ResultInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Update payment result data model.
 */
class Result implements ResultInterface
{
    /**
     * @var array
     */
    private $errors;

    /**
     * @var OrderPaymentInterface|null
     */
    private $payment;

    /**
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param $errors
     * @param OrderPaymentInterface|null $payment
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        $errors = [],
        OrderPaymentInterface $payment = null,
        ResultExtensionInterface $extensionAttributes = null
    ) {
        $this->errors = $errors;
        $this->payment = $payment;
        $this->extensionAttributes = $extensionAttributes;
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
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
