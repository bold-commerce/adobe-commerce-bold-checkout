<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Process order in case of self-hosted checkout.
 */
class ProcessOrder
{
    /**
     * @var Order
     */
    private $orderResource;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var ProcessOrderPayment
     */
    private $processOrderPayment;

    /**
     * @var OrderExtensionData
     */
    private $orderExtensionDataResource;

    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var AddCommentToOrder
     */
    private $addCommentToOrder;

    /**
     * @param Order $orderResource
     * @param ProcessOrderPayment $processOrderPayment
     * @param OrderInterfaceFactory $orderFactory
     * @param AddCommentToOrder $addCommentToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     */
    public function __construct(
        Order $orderResource,
        ProcessOrderPayment $processOrderPayment,
        OrderInterfaceFactory $orderFactory,
        AddCommentToOrder $addCommentToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->processOrderPayment = $processOrderPayment;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentToOrder = $addCommentToOrder;
    }

    /**
     * Process order in case of self-hosted checkout.
     *
     * @param OrderDataInterface $orderPayload
     * @return OrderInterface
     * @throws Exception
     */
    public function process(OrderDataInterface $orderPayload): OrderInterface
    {
        $attempt = 1;
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        do {
            $this->orderExtensionDataResource->load(
                $orderExtensionData,
                $orderPayload->getPublicId(),
                OrderExtensionDataResource::PUBLIC_ID
            );
            $orderId = $orderExtensionData->getOrderId();
            if (!$orderId) {
                $attempt++;
                sleep(1);
            }
        } while (!$orderId && $attempt < 3);
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);
        if (!$order->getId()) {
            throw new LocalizedException(__('Order not found'));
        }
        $this->processOrderPayment->process(
            $order,
            $orderPayload->getPayment(),
            $orderPayload->getTransaction()
        );
        $this->addCommentToOrder->addComment($order, $orderPayload);
        $orderExtensionData->setFulfillmentStatus($orderPayload->getFulfillmentStatus());
        $orderExtensionData->setFinancialStatus($orderPayload->getFinancialStatus());
        $this->orderExtensionDataResource->save($orderExtensionData);
        return $order;
    }
}
