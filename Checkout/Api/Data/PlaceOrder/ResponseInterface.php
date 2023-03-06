<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\ResponseExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place order response interface.
 */
interface ResponseInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve order from response.
     *
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    public function getOrder(): ?OrderInterface;

    /**
     * Retrieve errors from response.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Response\ErrorInterface[]
     */
    public function getErrors(): array;

    /**
     * Retrieve response extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\ResponseExtensionInterface|null
     */
    public function getExtensionAttributes(): ?ResponseExtensionInterface;
}
