<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\CompleteOrderInterface;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Perform after order submit actions.
 */
class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionDataResource
     */
    private $orderExtensionDataResource;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $boldPaymentMethods;

    /**
     * @var CompleteOrderInterface
     */
    private $completeOrder;

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param EventManagerInterface $eventManager
     * @param CompleteOrderInterface $completeOrder
     * @param array $boldPaymentMethods
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        EventManagerInterface $eventManager,
        CompleteOrderInterface $completeOrder,
        array $boldPaymentMethods = []
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->eventManager = $eventManager;
        $this->boldPaymentMethods = $boldPaymentMethods;
        $this->completeOrder = $completeOrder;
    }

    /**
     * Perform after order submit actions.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return;
        }
        if (!\in_array($order->getPayment()->getMethod(), \array_values($this->boldPaymentMethods), true)) {
            return;
        }
        $orderId = (int)$order->getEntityId();
        /** @var OrderDataInterface $orderPayload */
        $orderPayload = $observer->getEvent()->getOrderPayload();
        $publicOrderId = $orderPayload ? $orderPayload->getPublicId() : null;
        if (!$publicOrderId) {
            return;
        }
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $orderExtensionData->setOrderId($orderId);
        $orderExtensionData->setPublicId($publicOrderId);
        $this->eventManager->dispatch(
            'checkout_save_order_extension_data_before',
            ['order' => $order, 'orderExtensionData' => $orderExtensionData]
        );
        try {
            $this->orderExtensionDataResource->save($orderExtensionData);
        } catch (Exception $e) {
            return;
        }
        $this->completeOrder->execute($order);
    }
}
