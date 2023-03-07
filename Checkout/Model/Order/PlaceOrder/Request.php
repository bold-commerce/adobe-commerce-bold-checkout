<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\RequestExtensionInterface;
use Bold\Checkout\Api\Data\PlaceOrder\RequestInterface;

/**
 * Create order request data model.
 */
class Request implements RequestInterface
{
    /**
     * @var array
     */
    private $order;

    /**
     * @var RequestExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param OrderDataInterface $order
     * @param RequestExtensionInterface|null $extensionAttributes
     */
    public function __construct(OrderDataInterface $order, RequestExtensionInterface $extensionAttributes = null)
    {
        $this->order = $order;
        $this->extensionAttributes = $extensionAttributes;
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): OrderDataInterface
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?RequestExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
