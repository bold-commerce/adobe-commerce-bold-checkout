<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Create order from payload in case Bold-hosted checkout.
 */
class CreateOrderFromPayload
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
     * @var CreateOrderFromQuote
     */
    private $createOrderFromQuote;

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
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @param Order $orderResource
     * @param CreateOrderFromQuote $createOrderFromQuote
     * @param ProcessOrderPayment $processOrderPayment
     * @param OrderInterfaceFactory $orderFactory
     * @param AddCommentsToOrder $addCommentsToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param EventManagerInterface $eventManager
     */
    public function __construct(
        Order $orderResource,
        CreateOrderFromQuote $createOrderFromQuote,
        ProcessOrderPayment $processOrderPayment,
        OrderInterfaceFactory $orderFactory,
        AddCommentsToOrder $addCommentsToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        EventManagerInterface $eventManager
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->createOrderFromQuote = $createOrderFromQuote;
        $this->processOrderPayment = $processOrderPayment;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentsToOrder = $addCommentsToOrder;
        $this->eventManager = $eventManager;
    }

    /**
     * Create order from quote.
     *
     * @param OrderDataInterface $orderPayload
     * @param CartInterface $quote
     * @return OrderInterface
     * @throws Exception
     */
    public function createOrder(OrderDataInterface $orderPayload, CartInterface $quote): OrderInterface
    {
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $this->orderExtensionDataResource->load(
            $orderExtensionData,
            $orderPayload->getPublicId(),
            'public_id'
        );
        $magentoOrder = $this->orderFactory->create();
        $this->orderResource->load($magentoOrder, $orderExtensionData->getOrderId());
        if ($magentoOrder->getId()) {
            return $magentoOrder;
        }
        $magentoOrder = $this->createOrderFromQuote->create($quote, $orderPayload);
        $this->processOrderPayment->process(
            $magentoOrder,
            $orderPayload->getPayment(),
            $orderPayload->getTransaction()
        );
        $this->addCommentsToOrder->addComments($magentoOrder, $orderPayload);
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $orderExtensionData->setPublicId($orderPayload->getPublicId());
        $orderExtensionData->setOrderId((int)$magentoOrder->getId());
        $orderExtensionData->setFulfillmentStatus($orderPayload->getFulfillmentStatus());
        $orderExtensionData->setFinancialStatus($orderPayload->getFinancialStatus());

        $this->eventManager->dispatch(
            'create_order_from_payload_extension_data_save_before',
            ['orderPayload' => $orderPayload, 'orderExtensionData' => $orderExtensionData]
        );

        $this->orderExtensionDataResource->save($orderExtensionData);

        return $magentoOrder;
    }
}
