<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
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
     * @param OrderDataInterface $order
     */
    public function __construct(OrderDataInterface $order)
    {
        $this->order = $order;
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): OrderDataInterface
    {
        return $this->order;
    }
}
