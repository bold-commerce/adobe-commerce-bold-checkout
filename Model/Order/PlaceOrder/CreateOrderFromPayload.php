<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromPayload\CreateOrderFromQuote;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Create order from payload in case Bold-hosted checkout.
 */
class CreateOrderFromPayload
{
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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param CreateOrderFromQuote $createOrderFromQuote
     * @param ProcessOrderPayment $processOrderPayment
     * @param AddCommentsToOrder $addCommentsToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param EventManagerInterface $eventManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CreateOrderFromQuote $createOrderFromQuote,
        ProcessOrderPayment $processOrderPayment,
        AddCommentsToOrder $addCommentsToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        EventManagerInterface $eventManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->createOrderFromQuote = $createOrderFromQuote;
        $this->processOrderPayment = $processOrderPayment;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentsToOrder = $addCommentsToOrder;
        $this->eventManager = $eventManager;
        $this->orderRepository = $orderRepository;
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
        try {
            $magentoOrder = $this->orderRepository->get($orderExtensionData->getOrderId());
            if ($magentoOrder->getId()) {
                return $magentoOrder;
            }
        } catch (Exception $e) {
            $magentoOrder = null;
        }
        $quotePayment = $quote->getPayment();
        $quotePayment->setData(
            array_merge($quotePayment->getData(), $orderPayload->getPayment()->getData())
        );
        $magentoOrder = $this->createOrderFromQuote->create($quote, $orderPayload);
        $this->processOrderPayment->process(
            $magentoOrder,
            $orderPayload->getPayment(),
            $orderPayload->getTransaction()
        );
        $this->addCommentsToOrder->addComments($magentoOrder, $orderPayload);
        return $magentoOrder;
    }
}
