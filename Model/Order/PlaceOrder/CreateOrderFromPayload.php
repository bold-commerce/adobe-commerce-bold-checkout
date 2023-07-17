<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\PlaceOrder;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Model\Order\OrderExtensionData;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\Order\PlaceOrder\Request\OrderMetadataProcessorPool;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Exception;
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
     * @var AddCommentToOrder
     */
    private $addCommentToOrder;

    /**
     * @var OrderMetadataProcessorPool
     */
    private $orderMetadataProcessorPool;

    /**
     * @param Order $orderResource
     * @param CreateOrderFromQuote $createOrderFromQuote
     * @param ProcessOrderPayment $processOrderPayment
     * @param OrderInterfaceFactory $orderFactory
     * @param AddCommentToOrder $addCommentToOrder
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param OrderMetadataProcessorPool $orderMetadataProcessorPool
     */
    public function __construct(
        Order $orderResource,
        CreateOrderFromQuote $createOrderFromQuote,
        ProcessOrderPayment $processOrderPayment,
        OrderInterfaceFactory $orderFactory,
        AddCommentToOrder $addCommentToOrder,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        OrderMetadataProcessorPool $orderMetadataProcessorPool
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->createOrderFromQuote = $createOrderFromQuote;
        $this->processOrderPayment = $processOrderPayment;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->addCommentToOrder = $addCommentToOrder;
        $this->orderMetadataProcessorPool = $orderMetadataProcessorPool;
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
        $this->addCommentToOrder->addComment($magentoOrder, $orderPayload);
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $orderExtensionData->setPublicId($orderPayload->getPublicId());
        $orderExtensionData->setOrderId((int)$magentoOrder->getId());
        $orderExtensionData->setFulfillmentStatus($orderPayload->getFulfillmentStatus());
        $orderExtensionData->setFinancialStatus($orderPayload->getFinancialStatus());
        $this->orderExtensionDataResource->save($orderExtensionData);

        if ($orderPayload->getExtensionAttributes() !== null) {
            $this->orderMetadataProcessorPool->process(
                $orderPayload->getExtensionAttributes(),
                $magentoOrder
            );
        }

        return $magentoOrder;
    }
}
