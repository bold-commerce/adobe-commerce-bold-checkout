<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterfaceFactory;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterfaceFactory;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\PlaceOrderInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\OrderPayloadValidator;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromPayload;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrder;
use Bold\Checkout\Model\Order\PlaceOrder\Progress;
use Bold\Checkout\Model\Quote\LoadAndValidate;
use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData;
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\TransactionInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

use function __;
use function sprintf;

/**
 * Place magento order with bold payment service.
 */
class PlaceOrder implements PlaceOrderInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var OrderPayloadValidator
     */
    private $orderPayloadValidator;

    /**
     * @var CreateOrderFromPayload
     */
    private $createOrderFromPayload;

    /**
     * @var ProcessOrder
     */
    private $processOrder;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var OrderDataInterfaceFactory
     */
    private $orderDataFactory;

    /**
     * @var OrderPaymentInterfaceFactory
     */
    private $paymentFactory;

    /**
     * @var TransactionInterfaceFactory
     */
    private $transactionFactory;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var QuoteExtensionDataFactory
     */
    private $quoteExtensionDataFactory;

    /**
     * @var QuoteExtensionData
     */
    private $quoteExtensionDataResource;

    /**
     * @param OrderPayloadValidator $orderPayloadValidator
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param CreateOrderFromPayload $createOrderFromPayload
     * @param ProcessOrder $processOrder
     * @param Progress $progress
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param LoadAndValidate $loadAndValidate
     * @param StoreManagerInterface $storeManager
     * @param ClientInterface $client
     * @param OrderDataInterfaceFactory $orderDataFactory
     * @param OrderPaymentInterfaceFactory $paymentFactory
     * @param TransactionInterfaceFactory $transactionFactory
     * @param CheckoutSession $checkoutSession
     * @param QuoteExtensionDataFactory $quoteExtensionDataFactory
     * @param QuoteExtensionData $quoteExtensionDataResource
     * @param ConfigInterface $config
     */
    public function __construct(
        OrderPayloadValidator $orderPayloadValidator,
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        CreateOrderFromPayload $createOrderFromPayload,
        ProcessOrder $processOrder,
        Progress $progress,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        LoadAndValidate $loadAndValidate,
        StoreManagerInterface $storeManager,
        ClientInterface $client,
        OrderDataInterfaceFactory $orderDataFactory,
        OrderPaymentInterfaceFactory $paymentFactory,
        TransactionInterfaceFactory $transactionFactory,
        CheckoutSession $checkoutSession,
        QuoteExtensionDataFactory $quoteExtensionDataFactory,
        QuoteExtensionData $quoteExtensionDataResource,
        ConfigInterface $config
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->orderPayloadValidator = $orderPayloadValidator;
        $this->createOrderFromPayload = $createOrderFromPayload;
        $this->processOrder = $processOrder;
        $this->progress = $progress;
        $this->loadAndValidate = $loadAndValidate;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->storeManager = $storeManager;
        $this->client = $client;
        $this->orderDataFactory = $orderDataFactory;
        $this->paymentFactory = $paymentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteExtensionDataFactory = $quoteExtensionDataFactory;
        $this->quoteExtensionDataResource = $quoteExtensionDataResource;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function place(string $shopId, OrderDataInterface $order): ResultInterface
    {
        if ($this->progress->isInProgress($order)) {
            return $this->getValidationErrorResponse(
                __('Order for cart id: "%1" already in progress.', $order->getQuoteId())->render()
            );
        }
        $this->progress->start($order);
        try {
            $this->orderPayloadValidator->validate($order);
            $quote = $this->loadAndValidate->load($shopId, $order->getQuoteId());
        } catch (LocalizedException $e) {
            $this->progress->stop($order);
            return $this->getValidationErrorResponse($e->getMessage());
        }
        try {
            $quoteExtensionData = $this->quoteExtensionDataFactory->create();
            $this->quoteExtensionDataResource->load(
                $quoteExtensionData,
                $quote->getId(),
                QuoteExtensionData::QUOTE_ID
            );
            $magentoOrder = $quoteExtensionData->getOrderCreated()
                ? $this->processOrder->process($order)
                : $this->createOrderFromPayload->createOrder($order, $quote);
        } catch (Exception $e) {
            $this->progress->stop($order);
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                            ]
                        ),
                    ],
                ]
            );
        }
        $this->progress->stop($order);
        return $this->responseFactory->create(
            [
                'order' => $magentoOrder,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function authorizeAndPlace(string $publicOrderId, string $quoteMaskId): ResultInterface
    {
        $websiteId = (int)$this->storeManager->getStore()->getWebsiteId();
        $shopId = $this->config->getShopId($websiteId) ?? '';

        if (!is_numeric($quoteMaskId) && strlen($quoteMaskId) === 32) {
            try {
                $quoteId = $this->maskedQuoteIdToQuoteId->execute($quoteMaskId);
            } catch (NoSuchEntityException $exception) {
                return $this->getValidationErrorResponse((string)__('Invalid quote mask ID "%1"', $quoteMaskId));
            }
        } else {
            $quoteId = $quoteMaskId;
        }

        try {
            $quote = $this->loadAndValidate->load($shopId, (int)$quoteId);
        } catch (LocalizedException $e) {
            return $this->getValidationErrorResponse($e->getMessage());
        }

        $authorizedPayments = $this->getAuthorizedPayments($publicOrderId, $websiteId);
        if (!$authorizedPayments['success']) {
            $errorType = $authorizedPayments['type'] ?? 'server.payment_authorization_error';
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $authorizedPayments['error'],
                                'code' => 422,
                                'type' => $errorType
                            ]
                        )
                    ]
                ]
            );
        }

        $firstTransaction = $authorizedPayments['data']['transactions'][0] ?? [];
        if (empty($firstTransaction) || $firstTransaction['status'] === 'failed') {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => 'Invalid Transaction',
                                'code' => 422,
                                'type' => 'server.payment_authorization_error'
                            ]
                        )
                    ]
                ]
            );
        }

        $orderData = $this->buildOrderData($firstTransaction, (int)$quoteId, $publicOrderId);

        try {
            $order = $this->createOrderFromPayload->createOrder($orderData, $quote);
        } catch (Exception $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                                'code' => 500,
                                'type' => 'server.create_order_error'
                            ]
                        )
                    ]
                ]
            );
        }

        $this->updateCheckoutSession($quote, $order);

        return $this->responseFactory->create(
            [
                'order' => $order
            ]
        );
    }

    /**
     * Build validation error response.
     *
     * @param string $message
     * @return ResultInterface
     */
    private function getValidationErrorResponse(string $message): ResultInterface
    {
        return $this->responseFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $message,
                            'code' => 422,
                            'type' => 'server.validation_error',
                        ]
                    ),
                ],
            ]
        );
    }

    private function getAuthorizedPayments(string $publicOrderId, int $websiteId): array
    {
        $url = sprintf('checkout/orders/{{shopId}}/%s/payments/auth/full', $publicOrderId);
        $result = $this->client->post($websiteId, $url, []);
        $errors = $result->getErrors();
        $response = $result->getBody();

        if (count($errors) > 0) {
            $error = $errors[0];
            $errorMessage = isset($error['message']) ? $error['message'] : 'Unknown error occurred';
            $errorType = isset($error['type']) ? $error['type'] : null;
            return ['success' => false, 'error' => $errorMessage, 'type' => $errorType];
        } else if (!isset($response['data']) || $response['data'] === null) {
            return ['success' => false, 'error' => 'No data found'];
        }

        return ['success' => true, 'data' => $response['data']];
    }

    // phpcs:ignore Magento2.Annotation.MethodAnnotationStructure.NoCommentBlock
    private function updateCheckoutSession(CartInterface $quote, OrderInterface $order): void
    {
        $this->checkoutSession->setLastQuoteId($quote->getId()); // @phpstan-ignore method.notFound
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId()); // @phpstan-ignore method.notFound
        $this->checkoutSession->setLastOrderId($order->getEntityId()); // @phpstan-ignore method.notFound
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId()); // @phpstan-ignore method.notFound
        $this->checkoutSession->setLastOrderStatus($order->getStatus()); // @phpstan-ignore method.notFound
    }

    /**
     * @param array{
     *      gateway: string,
     *      payment_id: string,
     *      amount: int,
     *      transaction_id: string,
     *      currency: string,
     *      step: string,
     *      status: 'success'|'',
     *      tender_type: string,
     *      tender_details: array{
     *          brand: string,
     *          last_four: string,
     *          bin: string,
     *          expiration: string
     *      },
     *      gateway_response_data: string[]
     *  } $firstTransaction
     */
    private function buildOrderData(array $firstTransaction, int $quoteId, string $publicOrderId): OrderDataInterface
    {
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $this->paymentFactory->create();

        $orderPayment->setBaseAmountPaid($firstTransaction['amount'] / 100);
        $orderPayment->setAmountPaid($firstTransaction['amount'] / 100);
        $orderPayment->setCcLast4($firstTransaction['tender_details']['last_four']);
        $orderPayment->setCcType($firstTransaction['tender_details']['brand']);

        $cardExpirationMonth = substr($firstTransaction['tender_details']['expiration'], 0, 2);
        $cardExpirationYear = substr($firstTransaction['tender_details']['expiration'], 3);
        $orderPayment->setCcExpMonth($cardExpirationMonth);
        $orderPayment->setCcExpYear($cardExpirationYear);

        $orderPayment->setAdditionalInformation(
            [
                'transaction_gateway' => $firstTransaction['gateway'],
                'transaction_payment_id' => $firstTransaction['payment_id']
            ]
        );
        $orderPayment->setIsTransactionClosed(true); // @phpstan-ignore method.notFound


        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionFactory->create();
        $transaction->setTxnId($firstTransaction['transaction_id']);
        $transaction->setTxnType(TransactionInterface::TYPE_PAYMENT); // TODO: verify this transaction type is correct

        /** @var OrderDataInterface $orderData */
        $orderData = $this->orderDataFactory->create();
        $orderData->setQuoteId($quoteId);
        $orderData->setPublicId($publicOrderId);
        $orderData->setPayment($orderPayment);
        $orderData->setTransaction($transaction);

        return $orderData;
    }
}
