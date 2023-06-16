<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway\Config;

use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 *  Bold Payment Title Value Handler.
 */
class TitleValueHandler implements ValueHandlerInterface
{
    const TITLE = 'PayPal';

    /**
     * @inheritDoc
     */
    public function handle(array $subject, $storeId = null)
    {
        $payment = $subject['payment'] ?? null;
        if (!$payment) {
            return self::TITLE;
        }
        $ccLast4 = $payment->getPayment()->getCcLast4();
        $ccType = $payment->getPayment()->getCcType();
        if (!$ccLast4 || !$ccType) {
            return self::TITLE;
        }
        return strlen($ccLast4) === 4
            ? $ccType . ': ••••• •••••• ' . $ccLast4
            : $ccType . ': ' . $ccLast4;
    }
}
