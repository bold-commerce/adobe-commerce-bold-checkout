<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Order;

use Bold\Checkout\Api\Data\Order\Payment\RequestInterface;
use Bold\Checkout\Api\Data\Order\Payment\ResultInterface;

/**
 * Interface for handling the update of payment details in Magento orders.
 *
 * This interface defines the contract for updating the payment information associated with an order in Magento
 * after the payment has been processed through Bold Checkout. It is primarily used to ensure that the payment
 * details recorded in Magento are synchronized with the actual transaction processed by Bold Checkout.
 *
 * The main method `update` accepts a shop ID and a payment request object, and returns a result object
 * indicating the outcome of the update operation. This is crucial for maintaining the integrity and consistency
 * of order payment data within the Magento system.
 *
 * The endpoint for this operation is typically '/V1/shops/:shopId/payments', and this interface is a part of the
 * Bold Checkout API module, which facilitates integration with the Bold Checkout payment processing service.
 *
 * Refer to the `webapi.xml` file of the Bold Checkout module for more details on the API configuration.
 *
 * @see \Bold\Checkout\Api\Data\Order\Payment\RequestInterface for the structure of the payment request object.
 * @see \Bold\Checkout\Api\Data\Order\Payment\ResultInterface for the structure of the result object returned by the update operation.
 * @see Bold/Checkout/etc/webapi.xml for the API endpoint configuration.
 * @api
 */
interface UpdatePaymentInterface
{
    /**
     * Update order payment data.
     *
     * @param string $shopId
     * @param \Bold\Checkout\Api\Data\Order\Payment\RequestInterface $payment
     * @return \Bold\Checkout\Api\Data\Order\Payment\ResultInterface
     */
    public function update(string $shopId, RequestInterface $payment): ResultInterface;
}
