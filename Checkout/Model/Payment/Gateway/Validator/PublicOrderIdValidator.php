<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Verify if order has "public order id".
 */
class PublicOrderIdValidator extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function validate(array $validationSubject): ResultInterface
    {
        $payment = $validationSubject['payment'];
        $order = $payment->getOrder();

        return $this->createResult(
            $order->getExtensionAttributes()->getPublicId() !== null,
            [
                __('Order with "%s" has no Bold public order id.', $order->getIncrementId),
            ]
        );
    }
}
