<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Process order in case of self-hosted checkout.
 */
class ProcessOrder
{
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
     * @var AddCommentsToOrder
     */
    private $addCommentsToOrder;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @param ProcessOrderPayment $processOrderPayment
     * @param AddCommentsToOrder $addCommentsToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param OrderRepository $orderRepo
     */
    public function __construct(
        ProcessOrderPayment $processOrderPayment,
        AddCommentsToOrder $addCommentsToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        OrderRepository $orderRepo
    ) {
        $this->processOrderPayment = $processOrderPayment;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentsToOrder = $addCommentsToOrder;
        $this->orderRepository = $orderRepo;
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

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException|InputException $e) {
            throw new LocalizedException(__('Order not found'), $e);
        }

        $this->processOrderPayment->process(
            $order,
            $orderPayload->getPayment(),
            $orderPayload->getTransaction()
        );
        $this->addCommentsToOrder->addComments($order, $orderPayload);
        return $order;
    }
}
