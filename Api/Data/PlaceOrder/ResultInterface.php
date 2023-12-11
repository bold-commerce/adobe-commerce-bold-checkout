<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place order response interface. Represents a response data from the /V1/shops/:shopId/orders endpoint.
 * @see Bold/Checkout/etc/webapi.xml
 * @see \Bold\Checkout\Api\PlaceOrderInterface::place()
 * @api
 */
interface ResultInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve Magento order from response.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder(): ?OrderInterface;

    /**
     * Retrieve errors from response.
     *
     * @return \Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve response extension attributes. Used in case additional fields are returned by the API.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\ResultExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResultExtensionInterface;
}
