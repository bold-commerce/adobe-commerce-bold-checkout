<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
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
     * @var CreateInvoice
     */
    private $createInvoice;

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
     * @param CreateInvoice $createInvoice
     * @param OrderInterfaceFactory $orderFactory
     * @param AddCommentToOrder $addCommentToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     */
    public function __construct(
        Order $orderResource,
        ProcessOrderPayment $processOrderPayment,
        CreateInvoice $createInvoice,
        OrderInterfaceFactory $orderFactory,
        AddCommentToOrder $addCommentToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->processOrderPayment = $processOrderPayment;
        $this->createInvoice = $createInvoice;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentToOrder = $addCommentToOrder;
    }

    /**
     * Process order in case of self-hosted checkout.
     *
     * @param OrderDataInterface $orderPayload
     * @return OrderInterface
     * @throws LocalizedException
     */
    public function process(OrderDataInterface $orderPayload): OrderInterface
    {
        $attempt = 1;
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        do {
            $this->orderExtensionDataResource->load(
                $orderExtensionData,
                $orderPayload->getPublicId(),
                'public_id'
            );
            $orderId = $orderExtensionData->getOrderId();
            if (!$orderId) {
                $attempt++;
                sleep(1);
            }
        } while (!$orderId && $attempt > 5);
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);
        if (!$order->getId()) {
            throw new LocalizedException(__('Order not found'));
        }
        $this->processOrderPayment->process($order, $orderPayload);
        $this->createInvoice->create($order);
        $this->addCommentToOrder->addComment($order, $orderPayload);
        $orderExtensionData->setFulfillmentStatus($orderPayload->getFulfillmentStatus());
        $orderExtensionData->setFinancialStatus($orderPayload->getFinancialStatus());
        $this->orderExtensionDataResource->save($orderExtensionData);
        return $order;
    }
}
