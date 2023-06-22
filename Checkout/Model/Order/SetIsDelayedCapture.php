<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Set order is using delayed payment capture.
 */
class SetIsDelayedCapture
{
    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionFactory;

    /**
     * @var OrderExtensionDataResource
     */
    private $orderExtensionDataResource;

    /**
     * @param OrderExtensionDataFactory $orderExtensionFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionFactory,
        OrderExtensionDataResource $orderExtensionDataResource
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
    }

    /**
     * Set order is using delayed payment capture flag.
     *
     * @param OrderInterface $order
     * @throws AlreadyExistsException
     */
    public function set(OrderInterface $order): void
    {
        $orderExtensionData = $this->orderExtensionFactory->create();
        $this->orderExtensionDataResource->load(
            $orderExtensionData,
            $order->getId(),
            OrderExtensionDataResource::ORDER_ID
        );
        if ($orderExtensionData->getOrderId()) {
            $orderExtensionData->setIsDelayedCapture(1);
            $this->orderExtensionDataResource->save($orderExtensionData);
        }
    }
}
