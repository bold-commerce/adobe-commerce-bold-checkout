<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResponseInterfaceFactory;
use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\PlaceOrderInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Order\PlaceOrder\CreateInvoice;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromQuote;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrderPayment;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\ResourceModel\Order;

/**
 * Place magento order with bold payment service.
 */
class PlaceOrder implements PlaceOrderInterface
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
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @param ShopIdValidator $shopIdValidator
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
        ShopIdValidator $shopIdValidator,
        Order $orderResource,
        CartRepositoryInterface $cartRepository,
        CreateOrderFromQuote $createOrderFromQuote,
        ProcessOrderPayment $processOrderPayment,
        CreateInvoice $createInvoice,
        OrderInterfaceFactory $orderFactory,
        ResponseInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory
    ) {
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->cartRepository = $cartRepository;
        $this->createOrderFromQuote = $createOrderFromQuote;
        $this->processOrderPayment = $processOrderPayment;
        $this->createInvoice = $createInvoice;
        $this->shopIdValidator = $shopIdValidator;
    }

    /**
     * @inheritDoc
     */
    public function place(string $shopId, OrderDataInterface $order): ResponseInterface
    {
        try {
            $quote = $this->cartRepository->get($order->getQuoteId());
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                                'code' => 422,
                                'type' => 'server.validation_error',
                            ]
                        ),
                    ],
                ]
            );
        }
        $magentoOrder = $this->orderFactory->create();
        $this->orderResource->load($magentoOrder, $order->getOrderNumber(), 'ext_order_id');
        if ($magentoOrder->getId()) {
            return $this->getSuccessResponse($magentoOrder);
        }
        try {
            $magentoOrder = $this->createOrderFromQuote->create($quote, $order);
            $this->processOrderPayment->process($magentoOrder, $order);
            $this->createInvoice->create($magentoOrder);
            if ($quote->getBaseGrandTotal() !== $order->getTotal()) {
                $this->addCommentToOrder($magentoOrder, $order);
            }
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage());
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
     * @param string $message
     * @return ResponseInterface
     */
    private function getErrorResponse(string $message): ResponseInterface
    {
        return $this->responseFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
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
