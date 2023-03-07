<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\ResponseExtensionInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface;
use Bold\Checkout\Api\Data\Response\ErrorInterface;
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
     * @var ResponseExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param OrderInterface|null $order
     * @param ErrorInterface[] $errors
     * @param ResponseExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        OrderInterface $order = null,
        array $errors = [],
        ResponseExtensionInterface $extensionAttributes = null
    ) {
        $this->order = $order;
        $this->errors = $errors;
        $this->extensionAttributes = $extensionAttributes;
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

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes(): ?ResponseExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
