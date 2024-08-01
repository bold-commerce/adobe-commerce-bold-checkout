<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Psr\Log\LoggerInterface;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterface;
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
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_merge;
use function count;
use function explode;
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
     * @var Logger
     */
    private $logger;


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
        ConfigInterface $config,
        LoggerInterface $logger
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
        $this->logger = $logger;
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
        $setupTime = microtime(true);
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

        $endSetupTime = microtime(true);
        $this->logger->info('Time to setup: ' . ($endSetupTime - $setupTime));

        $startValidateTime = microtime(true);
        try {
            $quote = $this->loadAndValidate->load($shopId, (int)$quoteId);
        } catch (LocalizedException $e) {
            return $this->getValidationErrorResponse($e->getMessage());
        }

        if ($quote->getId() === null) {
            return $this->getValidationErrorResponse((string)__('Could not find quote with ID "%1"', $quoteId));
        }
        $endValidateTime = microtime(true);
        $this->logger->info('Time to validate: ' . ($endValidateTime - $startValidateTime));

        $startAuthorization = microtime(true);
        try {
            $startAPICall = microtime(true);
            $authorizedPayments = $this->getAuthorizedPayments($publicOrderId, $websiteId);
            $firstTransaction = $this->getFirstTransAction($authorizedPayments);
            $this->logger->info('Authorized Payments' . json_encode($authorizedPayments));
            $endAPICall = microtime(true);
            $this->logger->info('Time to API call: ' . ($endAPICall - $startAPICall));
        } catch (LocalizedException | Exception $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                                'code' => 500,
                                'type' => 'server.payment_authorization_error'
                            ]
                        )
                    ]
                ]
            );
        }

        $endAuthorization = microtime(true);
        $this->logger->info('Time to authorize: ' . ($endAuthorization - $startAuthorization));

        $buildOrderDataStart = microtime(true);
        $orderData = $this->buildOrderData($firstTransaction, (int)$quoteId, $publicOrderId);
        $buildOrderDataStart = microtime(true);
        $this->logger->info('Time to build order data: ' . ($buildOrderDataStart - $buildOrderDataStart));

        $createAndUpdate = microtime(true);
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

        $endCreateAndUpdate = microtime(true);
        $this->logger->info('Time to create and update: ' . ($endCreateAndUpdate - $createAndUpdate));
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

    /**
     * @return array{
     *      data?: array{
     *          total: int,
     *          transactions: array{
     *              gateway: string,
     *              payment_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              currency: string,
     *              step: string,
     *              status: 'success'|'failed'|'',
     *              tender_type: string,
     *              tender_details: array{
     *                  brand: string,
     *                  last_four: string,
     *                  bin: string,
     *                  expiration: string
     *              },
     *              gateway_response_data: string[]
     *          }[]
     *      },
     *      errors?: array{
     *          message: string,
     *          type: string,
     *          field: string,
     *          severity: string,
     *          sub_type: string,
     *          code?: string,
     *          transactions?: array{
     *              gateway: string,
     *              payment_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              currency: string,
     *              step: string,
     *              status: 'success'|'failed'|'',
     *              tender_type: string,
     *              tender_details: array{
     *                  brand: string,
     *                  last_four: string,
     *                  bin: string,
     *                  expiration: string
     *              },
     *              gateway_response_data: string[]
     *          }[]
     *      }[]
     *  }
     * @throws Exception
     */
    private function getAuthorizedPayments(string $publicOrderId, int $websiteId): array
    {
        $url = sprintf('checkout/orders/{{shopId}}/%s/payments/auth/full', $publicOrderId);
        $result = $this->client->post($websiteId, $url, []);

        $this->logger->info('Response: ' . json_encode($result));
        $response = $result->getBody();

        if (isset($response['errors']) && !empty($response['errors'])) {
            $error = $response['errors'][0];
            $errorMessage = isset($error['message']) ? $error['message'] : 'Unknown error occurred';
            throw new LocalizedException($errorMessage);
        } else if (!isset($response['data']) || $response['data'] === null) {
            throw new LocalizedException('No data found');
        }

        return $response;
    }

    private function getFirstTransAction(array $transactions): array
    {
        $firstTransaction = $transactions['data']['transactions'][0] ?? null;
        if($firstTransaction === null) {
            throw new LocalizedException('No transactions found');
        }
        if($firstTransaction['status'] === 'failed') {
            throw new LocalizedException('First transaction failed');
        }
        return $firstTransaction;
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
        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionFactory->create();
        /** @var OrderDataInterface $orderData */
        $orderData = $this->orderDataFactory->create();
        [$cardExpirationMonth, $cardExpirationYear] = explode(
            '/',
            $firstTransaction['tender_details']['expiration'],
            2
        );

        $orderPayment->setBaseAmountPaid($firstTransaction['amount'] / 100);
        $orderPayment->setAmountPaid($firstTransaction['amount'] / 100);
        $orderPayment->setCcLast4($firstTransaction['tender_details']['last_four']);
        $orderPayment->setCcType($firstTransaction['tender_details']['brand']);
        $orderPayment->setCcExpMonth($cardExpirationMonth);
        $orderPayment->setCcExpYear($cardExpirationYear);
        $orderPayment->setAdditionalInformation(
            [
                'transaction_gateway' => $firstTransaction['gateway'],
                'transaction_payment_id' => $firstTransaction['payment_id']
            ]
        );
        $orderPayment->setIsTransactionClosed(true); // @phpstan-ignore method.notFound

        $transaction->setTxnId($firstTransaction['transaction_id']);
        $transaction->setTxnType(TransactionInterface::TYPE_PAYMENT); // TODO: verify this transaction type is correct

        $orderData->setQuoteId($quoteId);
        $orderData->setPublicId($publicOrderId);
        $orderData->setPayment($orderPayment);
        $orderData->setTransaction($transaction);

        return $orderData;
    }
}
