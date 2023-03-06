<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer;

use Bold\Checkout\Model\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\OrderExtensionData;
use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Save additional bold order data to db.
 */
class SaveOrderBoldDataObserver implements ObserverInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionData $orderExtensionDataResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionData $orderExtensionDataResource,
        LoggerInterface $logger
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->logger = $logger;
    }

    /**
     * Save bold order data.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getExtensionAttributes()->getPublicId()) {
            return;
        }
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $this->orderExtensionDataResource->load($orderExtensionData, $order->getId());
        $orderExtensionData->setOrderId((int)$order->getId());
        $orderExtensionData->setPublicId($order->getExtensionAttributes()->getPublicId());
        $orderExtensionData->setFinancialStatus($order->getExtensionAttributes()->getFinancialStatus());
        $orderExtensionData->setFulfillmentStatus($order->getExtensionAttributes()->getFulfillmentStatus());
        try {
            $this->orderExtensionDataResource->save($orderExtensionData);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
