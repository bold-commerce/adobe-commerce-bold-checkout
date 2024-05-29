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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\FlagFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteId;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterfaceFactory;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Api\Data\TransactionInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;

use function __;
use function array_filter;
use function array_key_exists;
use function array_map;
use function count;
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
    private FlagFactory $flagFactory;
    private FlagResource $flagResource;

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
        FlagFactory $flagFactory,
        FlagResource $flagResource,
        OrderDataInterfaceFactory $orderDataFactory,
        OrderPaymentInterfaceFactory $paymentFactory,
        TransactionInterfaceFactory $transactionFactory
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
        $this->flagFactory = $flagFactory;
        $this->flagResource = $flagResource;
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

        $authorizedPayments = $this->getTestAuthorizedPayments();

        if ($authorizedPayments === null) {
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
        }

        if (array_key_exists('errors', $authorizedPayments) && count($authorizedPayments['errors']) > 0) {
            return $this->responseFactory->create(
                [
                    'errors' => array_map(
                        /**
                         * @param array{
                         *     code: string,
                         *     type: string,
                         *     message: string,
                         *     transactions: array{
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
                                    'code' => (int)$error['code'],
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
             *     gateway_id: string,
             *     amount: int,
             *     transaction_id: string,
             *     reference_transaction_id: string|null,
             *     response_code: string,
             *     status: 'success'|'failure'
             * } $transaction
             */
            static fn (array $transaction): bool => $transaction['status'] === 'success'
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
         *     gateway_id: string,
         *     amount: int,
         *     transaction_id: string,
         *     reference_transaction_id: string|null,
         *     response_code: string,
         *     status: 'success'
         * } $firstTransaction
         */
        $firstTransaction = array_shift($transactions);
        /** @var OrderPaymentInterface $orderPayment */
        $orderPayment = $this->paymentFactory->create();
        /** @var TransactionInterface $transaction */
        $transaction = $this->transactionFactory->create();
        /** @var OrderDataInterface $orderData */
        $orderData = $this->orderDataFactory->create();

        $orderPayment->setAmountPaid($firstTransaction['amount'] / 100);

        $transaction->setTxnId($firstTransaction['transaction_id']);
        $transaction->setTxnType(TransactionInterface::TYPE_PAYMENT); // TODO: verify this transaction type is correct
        /** @noinspection PhpUnhandledExceptionInspection */
        $transaction->setAdditionalInformation('gateway', $firstTransaction['gateway']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $transaction->setAdditionalInformation('gateway_id', $firstTransaction['gateway_id']);
        /** @noinspection PhpUnhandledExceptionInspection */
        $transaction->setAdditionalInformation('response_code', $firstTransaction['response_code']);

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
     * Get data set while running functional tests on this API endpoint.
     *
     * Note: this is a dirty hack. We wish it hadn't come to this, because of the way functional tests work in Magento,
     * we have no other choice.
     *
     * @return null|array{
     *      data?: array{
     *          total: int,
     *          transactions: array{
     *              gateway: string,
     *              gateway_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              reference_transaction_id: string|null,
     *              response_code: string,
     *              status: 'success'|'failure'
     *          }[]
     *      },
     *      errors?: array{
     *          code: string,
     *          type: string,
     *          message: string,
     *          transactions: array{
     *              gateway: string,
     *              gateway_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              reference_transaction_id: string|null,
     *              response_code: string,
     *              status: 'success'|'failure'
     *          }[]
     *      }
     *  }
     */
    private function getTestAuthorizedPayments(): ?array
    {
        /** @var Flag $testDataFlag */
        $testDataFlag = $this->flagFactory->create();

        $this->flagResource->load($testDataFlag, 'bold_api_test_data_payment_auth', 'flag_code');

        /**
         * @var null|array{
         *       data?: array{
         *           total: int,
         *           transactions: array{
         *               gateway: string,
         *               gateway_id: string,
         *               amount: int,
         *               transaction_id: string,
         *               reference_transaction_id: string|null,
         *               response_code: string,
         *               status: 'success'|'failure'
         *           }[]
         *       },
         *       errors?: array{
         *           code: string,
         *           type: string,
         *           message: string,
         *           transactions: array{
         *               gateway: string,
         *               gateway_id: string,
         *               amount: int,
         *               transaction_id: string,
         *               reference_transaction_id: string|null,
         *               response_code: string,
         *               status: 'success'|'failure'
         *           }[]
         *       }
         * } $testAuthorizedPayments
         */
        $testAuthorizedPayments = $testDataFlag->getFlagData();

        return $testAuthorizedPayments;
    }

    /**
     * @return null|array{
     *      data?: array{
     *          total: int,
     *          transactions: array{
     *              gateway: string,
     *              gateway_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              reference_transaction_id: string|null,
     *              response_code: string,
     *              status: 'success'|'failure'
     *          }[]
     *      },
     *      errors?: array{
     *          code: string,
     *          type: string,
     *          message: string,
     *          transactions: array{
     *              gateway: string,
     *              gateway_id: string,
     *              amount: int,
     *              transaction_id: string,
     *              reference_transaction_id: string|null,
     *              response_code: string,
     *              status: 'success'|'failure'
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

        return [
            'errors' => $errors,
            'data' => $result->getBody()
        ];
    }
}
