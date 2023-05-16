<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Add bold data to magento order observer.
 */
class AddBoldDataToOrderObserver implements ObserverInterface
{
    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionData
     */
    private $orderExtensionDataResource;

    /**
     * @var array
     */
    private $orderExtensionData = [];

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionData $orderExtensionDataResource
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionData $orderExtensionDataResource
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
    }

    /**
     * Add bold order data to magento order.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        $orderExtensionData = $this->orderExtensionData[$order->getId()] ?? null;
        if (!$orderExtensionData) {
            $orderExtensionData = $this->orderExtensionDataFactory->create();
            $this->orderExtensionDataResource->load($orderExtensionData, $order->getId(), OrderExtensionData::ORDER_ID);
            $this->orderExtensionData[$order->getId()] = $orderExtensionData;
        }
        $order->getExtensionAttributes()->setPublicId($orderExtensionData->getPublicId());
        $order->getExtensionAttributes()->setFulfillmentStatus($orderExtensionData->getFulfillmentStatus());
        $order->getExtensionAttributes()->setFinancialStatus($orderExtensionData->getFinancialStatus());
    }
}
