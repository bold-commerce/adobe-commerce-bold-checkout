<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Save order extension data.
 */
class SaveOrderExtensionDataObserver implements ObserverInterface
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
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var array
     */
    private $boldPaymentMethods;

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param Session $checkoutSession
     * @param EventManagerInterface $eventManager
     * @param array $boldPaymentMethods
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        Session $checkoutSession,
        EventManagerInterface $eventManager,
        array $boldPaymentMethods = []
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->checkoutSession = $checkoutSession;
        $this->eventManager = $eventManager;
        $this->boldPaymentMethods = $boldPaymentMethods;
    }

    /**
     * Save order extension data.
     *
     * @param Observer $observer
     * @return void
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
        $publicOrderId = $this->checkoutSession->getBoldCheckoutData()['data']['public_order_id'] ?? null;
        $this->checkoutSession->setBoldCheckoutData(null);
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
    }
}
