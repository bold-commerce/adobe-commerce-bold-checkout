<?php

declare(strict_types=1);

namespace Bold\Checkout\Test\Integration\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterface as PlaceOrderResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Order\PlaceOrder;
use Bold\Checkout\Model\Quote\LoadAndValidate;
use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

use function reset;

final class PlaceOrderTest extends TestCase // phpcs:ignore Magento2.PHP.FinalImplementation.FoundFinal
{
    use ArraySubsetAsserts;

    private CartInterface $quote;

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testAuthorizesAndPlacesOrderSuccessfully(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $loadAndValidateMock = $this->createMock(LoadAndValidate::class);
        $boldCheckoutApiClientMock = $this->createMock(ClientInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $placeOrderService = $objectManager->create(
            PlaceOrder::class,
            [
                'config' => $configMock,
                'loadAndValidate' => $loadAndValidateMock,
                'client' => $boldCheckoutApiClientMock,
            ]
        );
        $boldCheckoutApiResultMock = $this->createMock(ResultInterface::class);

        $configMock->method('getShopId')
            ->willReturn('74e51be84d1643e8a89df356b80bf2b5');

        $loadAndValidateMock->method('load')
            ->willReturn($this->getQuote());

        $boldCheckoutApiClientMock->method('post')
            ->willReturn($boldCheckoutApiResultMock);

        $boldCheckoutApiResultMock->method('getBody')
            ->willReturn(
                [
                    'data' => [
                        'total' => 3000,
                        'transactions' => [
                            [
                                'gateway' => 'Test Payment Gateway',
                                'payment_id' => 'ff2e05a2-04c7-4db3-9a3d-c15f5dcca7fe',
                                'amount' => 3000,
                                'transaction_id' => 'b9f35c91-1c16-4a3e-a985-a6a1af44c0ac',
                                'status' => 'success',
                                'tender_type' => 'credit_card',
                                'tender_details' => [
                                    'brand' => 'Discover',
                                    'last_four' => '0009',
                                    'bin' => '601100',
                                    'expiration' => '04/2030'
                                ],
                                'gateway_response_data' => []
                            ]
                        ]
                    ]
                ]
            );
        $boldCheckoutApiResultMock->method('getErrors')
            ->willReturn([]);

        /** @var PlaceOrderResultInterface $response */
        $response = $placeOrderService->authorizeAndPlace(
            'd407dc80-3470-49a4-9969-7a12cf17fd4a',
            $this->getQuoteMaskId()
        );

        /** @var OrderInterface|null $order */
        $order = $response->getOrder();
        /** @var OrderPaymentInterface|Payment $payment */
        $payment = $order?->getPayment() ?? $objectManager->create(OrderPaymentInterface::class);
        /** @var CheckoutSession $checkoutSession */
        $checkoutSession = $objectManager->get(CheckoutSession::class);

        self::assertEmpty($response->getErrors());
        self::assertNotNull($order);
        self::assertSame(30, $payment->getBaseAmountPaid());
        self::assertSame(30, $payment->getAmountPaid());
        self::assertSame('0009', $payment->getCcLast4());
        self::assertSame('Discover', $payment->getCcType());
        self::assertSame('04', $payment->getCcExpMonth());
        self::assertSame('2030', $payment->getCcExpYear());
        self::assertArraySubset(
            [
                'transaction_gateway' => 'Test Payment Gateway',
                'transaction_payment_id' => 'ff2e05a2-04c7-4db3-9a3d-c15f5dcca7fe'
            ],
            $payment->getAdditionalInformation()
        );
        self::assertSame('b9f35c91-1c16-4a3e-a985-a6a1af44c0ac', $payment->getLastTransId());
        self::assertTrue($payment->getIsTransactionClosed()); // @phpstan-ignore method.notFound
        self::assertSame($order->getQuoteId(), $checkoutSession->getLastQuoteId());
        self::assertSame($order->getQuoteId(), $checkoutSession->getLastSuccessQuoteId());
        self::assertSame($order->getEntityId(), $checkoutSession->getLastOrderId());
        self::assertSame($order->getIncrementId(), $checkoutSession->getLastRealOrderId());
        self::assertSame($order->getStatus(), $checkoutSession->getLastOrderStatus());
    }

    public function testDoesNotAuthorizeAndPlaceSuccessfullyIfQuoteMaskIdIsInvalid(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $boldCheckoutApiClientMock = $this->createMock(ClientInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $placeOrderService = $objectManager->create(
            PlaceOrder::class,
            [
                'config' => $configMock,
                'client' => $boldCheckoutApiClientMock,
            ]
        );
        $publicOrderId = 'fe90e903-e327-4ff4-ad31-c22529e33e50';
        $quoteMaskId = '22b2a1667c47450ea14d7d435fc2b087';
        $expectedErrorData = [
            'message' => "Invalid quote mask ID \"$quoteMaskId\"",
            'code' => 422,
            'type' => 'server.validation_error'
        ];

        $configMock->method('getShopId')
            ->willReturn('74e51be84d1643e8a89df356b80bf2b5');

        $response = $placeOrderService->authorizeAndPlace($publicOrderId, $quoteMaskId);
        $actualErrorData = [
            'code' => $response->getErrors()[0]->getCode(),
            'message' => $response->getErrors()[0]->getMessage(),
            'type' => $response->getErrors()[0]->getType()
        ];

        self::assertEquals($expectedErrorData, $actualErrorData);
        self::assertNull($response->getOrder());
    }

    public function testDoesNotAuthorizeAndPlaceSuccessfullyIfQuotDoesNotExist(): void
    {
        $configMock = $this->createMock(ConfigInterface::class);
        $maskedQuoteIdToQuoteIdMock = $this->createMock(MaskedQuoteIdToQuoteIdInterface::class);
        $loadAndValidateMock = $this->createMock(LoadAndValidate::class);
        $boldCheckoutApiClientMock = $this->createMock(ClientInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $placeOrderService = $objectManager->create(
            PlaceOrder::class,
            [
                'config' => $configMock,
                'maskedQuoteIdToQuoteId' => $maskedQuoteIdToQuoteIdMock,
                'loadAndValidate' => $loadAndValidateMock,
                'client' => $boldCheckoutApiClientMock,
            ]
        );
        $publicOrderId = 'fe90e903-e327-4ff4-ad31-c22529e33e50';
        $quoteMaskId = '22b2a1667c47450ea14d7d435fc2b087';
        $expectedErrorData = [
            'message' => 'Could not find quote with ID "42"',
            'code' => 422,
            'type' => 'server.validation_error'
        ];

        $configMock->method('getShopId')
            ->willReturn('74e51be84d1643e8a89df356b80bf2b5');

        $maskedQuoteIdToQuoteIdMock->method('execute')
            ->willReturn(42);

        $loadAndValidateMock->method('load')
            ->willReturn($objectManager->create(CartInterface::class));

        $response = $placeOrderService->authorizeAndPlace($publicOrderId, $quoteMaskId);
        $actualErrorData = [
            'code' => $response->getErrors()[0]->getCode(),
            'message' => $response->getErrors()[0]->getMessage(),
            'type' => $response->getErrors()[0]->getType()
        ];

        self::assertEquals($expectedErrorData, $actualErrorData);
        self::assertNull($response->getOrder());
    }

    /**
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @dataProvider boldAuthorizedPaymentsApiResultDataProvider
     */
    public function testDoesNotAuthorizeAndPlaceSuccessfullyIfBoldApiReturnsError(
        array $authorizedPayments,
        array $expectedErrorData
    ): void {
        $configMock = $this->createMock(ConfigInterface::class);
        $loadAndValidateMock = $this->createMock(LoadAndValidate::class);
        $boldCheckoutApiClientMock = $this->createMock(ClientInterface::class);
        $objectManager = Bootstrap::getObjectManager();
        $placeOrderService = $objectManager->create(
            PlaceOrder::class,
            [
                'config' => $configMock,
                'loadAndValidate' => $loadAndValidateMock,
                'client' => $boldCheckoutApiClientMock,
            ]
        );
        $boldCheckoutApiResultMock = $this->createMock(ResultInterface::class);

        $configMock->method('getShopId')
            ->willReturn('74e51be84d1643e8a89df356b80bf2b5');

        $loadAndValidateMock->method('load')
            ->willReturn($this->getQuote());

        $boldCheckoutApiClientMock->method('post')
            ->willReturn($boldCheckoutApiResultMock);

        $boldCheckoutApiResultMock->method('getBody')
            ->willReturn($authorizedPayments['data'] ?? []);
        $boldCheckoutApiResultMock->method('getErrors')
            ->willReturn($authorizedPayments['errors'] ?? []);

        /** @var PlaceOrderResultInterface $response */
        $response = $placeOrderService->authorizeAndPlace(
            'fe90e903-e327-4ff4-ad31-c22529e33e50',
            $this->getQuoteMaskId()
        );
        $actualErrorData = [
            'code' => $response->getErrors()[0]->getCode(),
            'message' => $response->getErrors()[0]->getMessage(),
            'type' => $response->getErrors()[0]->getType()
        ];

        self::assertEquals($expectedErrorData, $actualErrorData);
        self::assertNull($response->getOrder());
    }

    public function boldAuthorizedPaymentsApiResultDataProvider(): array
    {
        return [
            'failed transaction' => [
                'authorizedPayments' => [
                    'data' => [],
                    'errors' => [
                        [
                            'code' => '123',
                            'type' => 'declined',
                            'message' => 'Payment declined for insufficient funds',
                            'transactions' => [
                                'gateway' => 'Test Payment Gateway',
                                'payment_id' => 'd7fc6a17-04de-489c-a1ce-1d3571275791',
                                'amount' => 3000,
                                'transaction_id' => 'd8efbfac-e718-4f73-823a-6c9f4e44c4f0',
                                'status' => 'failed',
                                'tender_type' => 'credit_card',
                                'tender_details' => [
                                    'brand' => 'MasterCard',
                                    'last_four' => '5100',
                                    'bin' => '510510',
                                    'expiration' => '01/2029'
                                ],
                                'gateway_response_data' => []
                            ]
                        ]
                    ]
                ],
                'expectedErrorData' => [
                    'code' => '123',
                    'type' => 'declined',
                    'message' => 'Payment declined for insufficient funds',
                ]
            ],
            'failed and successful transactions' => [
                'authorizedPayments' => [
                    'data' => [
                        'total' => 3000,
                        'transactions' => [
                            [
                                'gateway' => 'Test Payment Gateway',
                                'payment_id' => 'ff2e05a2-04c7-4db3-9a3d-c15f5dcca7fe',
                                'amount' => 3000,
                                'transaction_id' => 'b9f35c91-1c16-4a3e-a985-a6a1af44c0ac',
                                'status' => 'success',
                                'tender_type' => 'credit_card',
                                'tender_details' => [
                                    'brand' => 'MasterCard',
                                    'last_four' => '0011',
                                    'bin' => '222300',
                                    'expiration' => '07/2027'
                                ],
                                'gateway_response_data' => []
                            ]
                        ]
                    ],
                    'errors' => [
                        [
                            'code' => '123',
                            'type' => 'declined',
                            'message' => 'Payment declined for insufficient funds',
                            'transactions' => [
                                'gateway' => 'Test Payment Gateway',
                                'payment_id' => 'd7fc6a17-04de-489c-a1ce-1d3571275791',
                                'amount' => 3000,
                                'transaction_id' => '3407d7b0-0029-43d5-bfa9-b3c8bb1267b8',
                                'status' => 'failed',
                                'tender_type' => 'credit_card',
                                'tender_details' => [
                                    'brand' => 'Visa',
                                    'last_four' => '1115',
                                    'bin' => '400011',
                                    'expiration' => '12/2025'
                                ],
                                'gateway_response_data' => []
                            ]
                        ]
                    ]
                ],
                'expectedErrorData' => [
                    'code' => '123',
                    'type' => 'declined',
                    'message' => 'Payment declined for insufficient funds',
                ]
            ]
        ];
    }

    protected function tearDown(): void
    {
        if (isset($this->quote)) {
            unset($this->quote);
        }
    }

    private function getQuote(): CartInterface
    {
        if (isset($this->quote)) {
            return $this->quote;
        }

        $objectManager = Bootstrap::getObjectManager();
        $searchCriteria = $objectManager->create(SearchCriteriaBuilder::class)
            ->addFilter('reserved_order_id', 'test_order_1')
            ->create();
        $quotes = $objectManager->create(CartRepositoryInterface::class)
            ->getList($searchCriteria)
            ->getItems();
        $this->quote = reset($quotes) ?: $objectManager->create(CartInterface::class);

        return $this->quote;
    }

    private function getQuoteMaskId(): string
    {
        $objectManager = Bootstrap::getObjectManager();

        return $objectManager->create(QuoteIdToMaskedQuoteIdInterface::class)
            ->execute((int)$this->getQuote()->getId());
    }
}
