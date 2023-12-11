<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\Order\Payment;

use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Update payment result interface.
 *
 * Represents a response data from the /V1/shops/:shopId/payments endpoint. @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\Order\UpdatePaymentInterface::update()
 * @api
 */
interface ResultInterface
{
    /**
     * Get payment object from the response.
     *
     * @return \Magento\Sales\Api\Data\OrderPaymentInterface|null
     */
    public function getPayment(): ?OrderPaymentInterface;

    /**
     * Retrieve errors from the response.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Get extension attributes from the response. Used in case additional fields are returned by the API.
     *
     * @return \Bold\Checkout\Api\Data\Order\Payment\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
