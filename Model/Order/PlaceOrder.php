<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

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
use Exception;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
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
     * @var ConfigInterface
     */
    private $config;

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
    private MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId;
    private StoreManagerInterface $storeManager;
    private ClientInterface $client;
    private OrderDataInterfaceFactory $orderDataFactory;
    private OrderPaymentInterfaceFactory $paymentFactory;
    private TransactionInterfaceFactory $transactionFactory;
    private CheckoutSession $checkoutSession;

    /**
     * @param OrderPayloadValidator $orderPayloadValidator
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ConfigInterface $config
     * @param CreateOrderFromPayload $createOrderFromPayload
     * @param ProcessOrder $processOrder
     * @param Progress $progress
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        OrderPayloadValidator $orderPayloadValidator,
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        ConfigInterface $config,
        CreateOrderFromPayload $createOrderFromPayload,
        ProcessOrder $processOrder,
        Progress $progress,
        MaskedQuoteIdToQuoteId $maskedQuoteIdToQuoteId,
        LoadAndValidate $loadAndValidate,
        StoreManagerInterface $storeManager,
        ClientInterface $client,
        OrderDataInterfaceFactory $orderDataFactory,
        OrderPaymentInterfaceFactory $paymentFactory,
        TransactionInterfaceFactory $transactionFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->orderPayloadValidator = $orderPayloadValidator;
        $this->config = $config;
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
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $magentoOrder = $this->config->isCheckoutTypeSelfHosted($websiteId)
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

        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($quoteMaskId);
        } catch (NoSuchEntityException) {
            return $this->getValidationErrorResponse((string)__('Invalid quote mask ID "%1"', $quoteMaskId));
        }

        try {
            $quote = $this->loadAndValidate->load($shopId, (int)$quoteId);
        } catch (LocalizedException $e) {
            return $this->getValidationErrorResponse($e->getMessage());
        }

        if ($quote->getId() === null) {
            return $this->getValidationErrorResponse((string)__('Could not find quote with ID "%1"', $quoteId));
        }

        try {
            $authorizedPayments = $this->getAuthorizedPayments($publicOrderId, $websiteId);
        } catch (Exception $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                                'code' => 500,
                                'type' => 'server.bold_checkout_api_error'
                            ]
                        )
                    ]
                ]
            );
        }

        if (array_key_exists('errors', $authorizedPayments) && count($authorizedPayments['errors']) > 0) {
            return $this->responseFactory->create(
                [
                    'errors' => array_map(
                        /**
                         * @param array{
                         *     message: string,
                         *     type: string,
                         *     field: string,
                         *     severity: string,
                         *     sub_type: string,
                         *     code?: string,
                         *     transactions?: array{
                         *         gateway: string,
                         *         gateway_id: string,
                         *         amount: int,
                         *         transaction_id: string,
                         *         reference_transaction_id: string|null,
                         *         response_code: string,
                         *         status: 'success'|'failure'
                         *     }[]
                         * } $error
                         */
                        function (array $error): ErrorInterface {
                            return $this->errorFactory->create(
                                [
                                    'code' => (int)($error['code'] ?? 0),
                                    'type' => $error['type'],
                                    'message' => $error['message']
                                ]
                            );
                        },
                        $authorizedPayments['errors']
                    )
                ]
            );
        }

        if (!array_key_exists('data', $authorizedPayments) || count($authorizedPayments['data']) === 0) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => __(
                                    'There are no authorized payments available for order "%1"',
                                    $publicOrderId
                                ),
                                'code' => 500,
                                'type' => 'server.bold_checkout_api_error'
                            ]
                        )
                    ]
                ]
            );
        }

        $transactions = array_filter(
            $authorizedPayments['data']['transactions'],
            /**
             * @param array{
             *     gateway: string,
             *     payment_id: string,
             *     amount: int,
             *     transaction_id: string,
             *     currency: string,
             *     step: string,
             *     status: 'success'|'failure'|'',
             *     tender_type: string,
             *     tender_details: array{
             *         brand: string,
             *         last_four: string,
             *         bin: string,
             *         expiration: string
             *     },
             *     gateway_response_data: string[]
             * } $transaction
             */
            static fn (array $transaction): bool => $transaction['status'] !== 'failed'
        );

        if (count($transactions) === 0) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => __(
                                    'There are no successful transactions available for order "%1"',
                                    $publicOrderId
                                ),
                                'code' => 500,
                                'type' => 'server.bold_checkout_api_error'
                            ]
                        )
                    ]
                ]
            );
        }

        /**
         * @var array{
         *     gateway: string,
         *     payment_id: string,
         *     amount: int,
         *     transaction_id: string,
         *     currency: string,
         *     step: string,
         *     status: 'success'|'',
         *     tender_type: string,
         *     tender_details: array{
         *         brand: string,
         *         last_four: string,
         *         bin: string,
         *         expiration: string
         *     },
         *     gateway_response_data: string[]
         * } $firstTransaction
         */
        $firstTransaction = array_shift($transactions);
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

        $transaction->setTxnId($firstTransaction['transaction_id']);
        $transaction->setTxnType(TransactionInterface::TYPE_PAYMENT); // TODO: verify this transaction type is correct
        $transaction->setIsClosed(1);

        $orderData->setQuoteId((int)$quoteId);
        $orderData->setPublicId($publicOrderId);
        $orderData->setPayment($orderPayment);
        $orderData->setTransaction($transaction);

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

    /**
     * @return null|array{
     *      data?: array{
     *          total: int,
     *          transactions: array{
     *              gateway: string,
     *              payment_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              currency: string,
     *              step: string,
     *              status: 'success'|'failure'|'',
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
     *              status: 'success'|'failure'|'',
     *              tender_type: string,
     *              tender_details: array{
     *                  brand: string,
     *                  last_four: string,
     *                  bin: string,
     *                  expiration: string
     *              },
     *              gateway_response_data: string[]
     *          }[]
     *      }
     *  }
     * @throws Exception
     */
    private function getAuthorizedPayments(string $publicOrderId, int $websiteId): array
    {
        $url = sprintf('checkout/orders/{{shopId}}/%s/payments/auth/full', $publicOrderId);
        $result = $this->client->post($websiteId, $url, []);
        $errors = $result->getErrors();

        if (array_key_exists('errors', $errors) && count($errors) > 0) {
            $errors = [
                [
                    'code' => $result->getStatus(),
                    'type' => 'server.bold_checkout_api_error',
                    'message' => $errors[0],
                    'transactions' => []
                ]
            ];
        }

        return array_merge($result->getBody(), ['errors' => $errors]);
    }

    private function updateCheckoutSession(CartInterface $quote, OrderInterface $order): void
    {
        $this->checkoutSession->setLastQuoteId($quote->getId());
        $this->checkoutSession->setLastSuccessQuoteId($quote->getId());
        $this->checkoutSession->setLastOrderId($order->getId());
        $this->checkoutSession->setLastRealOrderId($order->getIncrementId());
        $this->checkoutSession->setLastOrderStatus($order->getStatus());
    }
}
