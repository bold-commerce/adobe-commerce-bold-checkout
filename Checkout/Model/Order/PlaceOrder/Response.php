<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Response\ErrorInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Create order endpoint response.
 */
class Response implements ResponseInterface
{
    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @var ErrorInterface|null
     */
    private $errors;

    /**
     * @param OrderInterface|null $order
     * @param ErrorInterface[] $errors
     */
    public function __construct(OrderInterface $order = null, array $errors = [])
    {
        $this->order = $order;
        $this->errors = $errors;
    }

    /**
     * @inheritDoc
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
