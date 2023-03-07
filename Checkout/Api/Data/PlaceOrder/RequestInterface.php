<?php
declare(strict_types=1);

namespace Bold\Checkout\Api\Data\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\RequestExtensionInterface;
use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Order service request interface.
 */
interface RequestInterface extends ExtensibleDataInterface
{
    /**
     * Retrieve order request data.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface
     */
    public function getOrder(): OrderDataInterface;

    /**
     * Retrieve request extension attributes.
     *
     * @return \Bold\Checkout\Api\Data\PlaceOrder\RequestExtensionInterface|null
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface;
}
