<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultExtensionInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Create order endpoint response.
 */
class Result implements ResultInterface
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
     * @var ResultExtensionInterface|null
     */
    private $extensionAttributes;

    /**
     * @param OrderInterface|null $order
     * @param ErrorInterface[] $errors
     * @param ResultExtensionInterface|null $extensionAttributes
     */
    public function __construct(
        OrderInterface $order = null,
        array $errors = [],
        ResultExtensionInterface $extensionAttributes = null
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
    public function getExtensionAttributes(): ?ResultExtensionInterface
    {
        return $this->extensionAttributes;
    }
}
