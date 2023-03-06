<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterfaceFactory;
use Bold\Checkout\Api\PlaceOrderInterface;
use Bold\Checkout\Model\Order\PlaceOrder\CreateInvoice;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromQuote;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrderPayment;
use Bold\Checkout\Model\Order\PlaceOrder\SaveCustomerAddress;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Store\Model\App\Emulation;

/**
 * Place magento order with bold payment service.
 */
class PlaceOrder implements PlaceOrderInterface
{
    /**
     * @var Emulation
     */
    private $emulation;

    /**
     * @var Order
     */
    private $orderResource;

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @var ResponseInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CreateOrderFromQuote
     */
    private $createOrderFromQuote;

    /**
     * @var ProcessOrderPayment
     */
    private $processOrderPayment;

    /**
     * @var CreateInvoice
     */
    private $createInvoice;

    /**
     * @param Emulation $emulation
     * @param Order $orderResource
     * @param CartRepositoryInterface $cartRepository
     * @param CreateOrderFromQuote $createOrderFromQuote
     * @param ProcessOrderPayment $processOrderPayment
     * @param CreateInvoice $createInvoice
     * @param OrderInterfaceFactory $orderFactory
     * @param ResponseInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     */
    public function __construct(
        Emulation $emulation,
        Order $orderResource,
        CartRepositoryInterface $cartRepository,
        CreateOrderFromQuote $createOrderFromQuote,
        ProcessOrderPayment $processOrderPayment,
        CreateInvoice $createInvoice,
        OrderInterfaceFactory $orderFactory,
        ResponseInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->emulation = $emulation;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->cartRepository = $cartRepository;
        $this->createOrderFromQuote = $createOrderFromQuote;
        $this->processOrderPayment = $processOrderPayment;
        $this->createInvoice = $createInvoice;
    }

    /**
     * @inheritDoc
     */
    public function place(string $shopId, OrderDataInterface $order): ResponseInterface
    {
        $magentoOrder = $this->orderFactory->create();
        $this->orderResource->load($magentoOrder, $order->getOrderNumber(), 'ext_order_id');
        if ($magentoOrder->getId()) {
            return $this->getSuccessResponse($magentoOrder);
        }
        try {
            $quote = $this->cartRepository->getActive($order->getQuoteId());
        } catch (NoSuchEntityException $e) {
            return $this->getErrorResponse(500, 'server.internal_error', $e->getMessage());
        }
        $this->emulation->startEnvironmentEmulation($quote->getStoreId());
        try {
            $magentoOrder = $this->createOrderFromQuote->create($quote, $order);
            $this->processOrderPayment->process($magentoOrder, $order);
            $this->createInvoice->create($magentoOrder);
            if ($quote->getBaseGrandTotal() !== $order->getTotal()) {
                $this->addCommentToOrder($magentoOrder, $order);
            }
        } catch (Exception $e) {
            return $this->getErrorResponse(500, 'server.internal_error', $e->getMessage());
        }
        return $this->getSuccessResponse($magentoOrder);
    }

    /**
     * Add comment to order in case it's quote total is different from bold order total.
     *
     * @param OrderInterface $order
     * @param OrderDataInterface $orderData
     * @return void
     * @throws Exception
     */
    private function addCommentToOrder(
        OrderInterface $order,
        OrderDataInterface $orderData
    ) {
        $operation = $order->hasInvoices() ? 'refund' : 'cancel';
        $transactionType = $order->hasInvoices() ? 'payment' : 'authorization';
        $comment = __(
            'Please consider to %1 this order due to it\'s total = %2 mismatch %3 transaction amount = %4. '
            . 'For more details please refer to Bold Help Center at "https://support.boldcommerce.com"',
            $operation,
            $order->getBaseGrandTotal(),
            $transactionType,
            $orderData->getTotal()
        );
        $order->addCommentToStatusHistory($comment);
        $this->orderResource->save($order);
    }

    /**
     * Build error response.
     *
     * @param int $code
     * @param string $type
     * @param string $message
     * @return ResponseInterface
     */
    private function getErrorResponse(int $code, string $type, string $message): ResponseInterface
    {
        return $this->responseFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'code' => $code,
                            'type' => $type,
                            'message' => $message,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Build order response
     *
     * @param OrderInterface $order
     * @return ResponseInterface
     */
    private function getSuccessResponse(OrderInterface $order): ResponseInterface
    {
        return $this->responseFactory->create(
            [
                'order' => $order,
            ]
        );
    }
}
