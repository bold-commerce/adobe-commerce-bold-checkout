<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;

/**
 * Create order progress service.
 */
class Progress
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
     * @var OrderResource
     */
    private $orderResource;

    /**
     * @var OrderExtensionData|null
     */
    private $orderExtensionData = null;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param OrderResource $orderResource
     * @param OrderInterfaceFactory $orderFactory
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        OrderResource $orderResource,
        OrderInterfaceFactory $orderFactory
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
    }

    /**
     * Check if create order already in progress.
     *
     * @param OrderDataInterface $orderData
     * @return bool
     */
    public function isInProgress(OrderDataInterface $orderData): bool
    {
        $this->orderExtensionData = $this->orderExtensionDataFactory->create();
        $this->orderExtensionDataResource->load(
            $this->orderExtensionData,
            $orderData->getQuoteId(),
            OrderExtensionDataResource::QUOTE_ID
        );
        return $this->orderExtensionData->getId() !== null;
    }

    /**
     * Return created order if exists.
     *
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        if ($this->orderExtensionData->getOrderId()) {
            $order = $this->orderFactory->create();
            $this->orderResource->load($order, $this->orderExtensionData->getOrderId());
            return $order;
        }
        return null;
    }

    /**
     * Save order extension data.
     *
     * @param OrderDataInterface $orderData
     * @return void
     */
    public function start(OrderDataInterface $orderData): void
    {
        try {
            $this->orderExtensionData = $this->orderExtensionDataFactory->create();
            $this->orderExtensionDataResource->load(
                $this->orderExtensionData,
                $orderData->getPublicId(),
                OrderExtensionDataResource::PUBLIC_ID);
            $this->orderExtensionData->setPublicId($orderData->getPublicId());
            $this->orderExtensionData->setQuoteId($orderData->getQuoteId());
            $this->orderExtensionData->setFulfillmentStatus($orderData->getFulfillmentStatus());
            $this->orderExtensionData->setFinancialStatus($orderData->getFinancialStatus());
            $this->orderExtensionDataResource->save($this->orderExtensionData);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Delete order extension data.
     *
     * @return void
     */
    public function stop(): void
    {
        if ($this->orderExtensionData) {
            try {
                $this->orderExtensionDataResource->delete($this->orderExtensionData);
            } catch (Exception $e) {
                return;
            }
        }
    }
}
