<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;

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
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        Session $checkoutSession
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->checkoutSession = $checkoutSession;
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
        if ($order->getPayment()->getMethod() !== 'bold') {
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
        $orderExtensionData->setFulfillmentStatus('pending');
        $orderExtensionData->setFinancialStatus('pending');
        try {
            $this->orderExtensionDataResource->save($orderExtensionData);
        } catch (Exception $e) {
            return;
        }
    }
}
