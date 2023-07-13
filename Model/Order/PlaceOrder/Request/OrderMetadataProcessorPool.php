<?php

namespace Bold\Checkout\Model\Order\PlaceOrder\Request;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataExtensionInterface;
use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderMetadataProcessorInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Composite order data extension attributes processor
 */
class OrderMetadataProcessorPool implements OrderMetadataProcessorInterface
{
    /**
     * @var OrderMetadataProcessorInterface[]
     */
    private $processors;

    /**
     * @param OrderMetadataProcessorInterface[] $processors
     */
    public function __construct(
        array $processors
    )
    {
        $this->processors = $processors;
    }

    /**
     * Process order metadata through available processors.
     *
     * @param OrderDataExtensionInterface $orderDataExtension
     * @param OrderInterface $order
     * @return void
     */
    public function process(OrderDataExtensionInterface $orderDataExtension, OrderInterface $order): void
    {
        foreach ($this->processors as $name => $processor) {
            if (!($processor instanceof OrderMetadataProcessorInterface)) {
                throw new \InvalidArgumentException(
                    sprintf('Processor %s must implement %s interface.', $name, OrderMetadataProcessorInterface::class)
                );
            }
            $processor->process($orderDataExtension, $order);
        }
    }
}
