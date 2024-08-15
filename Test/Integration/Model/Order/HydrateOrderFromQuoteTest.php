<?php

declare(strict_types=1);

namespace Bold\Checkout\Test\Integration\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\HydrateOrderFromQuote;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\HTTP\ClientInterface as HttpClientInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

use function reset;

// phpcs:ignore Magento2.PHP.FinalImplementation.FoundFinal
final class HydrateOrderFromQuoteTest extends TestCase
{
    private ?CartInterface $quote = null;

    /**
     * @dataProvider hydratesOrderFromQuoteSuccessfullyDataProvider
     * @magentoDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testHydratesOrderFromQuoteSuccessfully(bool $quoteItemHasWeight): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $httpClientMock = $this->createMock(HttpClientInterface::class);
        $boldCheckoutApiResult = $objectManager->create(
            ResultInterface::class,
            [
                'client' => $httpClientMock,
            ]
        );
        $boldCheckoutApiClientMock = $this->createMock(ClientInterface::class);
        $publicOrderId = '164d20119a20438c8b6ec57d4bc3747b';
        $quote = $this->getQuote();
        /** @var CartItemInterface[] $quoteItems */
        $quoteItems = $quote->getItems();
        $productImageUrlBuilder = $objectManager->create(UrlBuilder::class);
        $hydratedOrderFromQuote = $objectManager->create(
            HydrateOrderFromQuote::class,
            [
                'client' => $boldCheckoutApiClientMock,
            ]
        );

        $boldCheckoutApiClientMock->expects(self::once())
            ->method('put')
            ->with(
                1,
                "checkout_sidekick/{{shopId}}/order/$publicOrderId",
                [
                    'billing_address' => [
                        'id' => null,
                        'business_name' => 'CompanyName',
                        'country_code' => 'US',
                        'country' => 'United States',
                        'city' => 'CityM',
                        'first_name' => 'John',
                        'last_name' => 'Smith',
                        'phone_number' => '3468676',
                        'postal_code' => '75477',
                        'province' => 'Alabama',
                        'province_code' => 'AL',
                        'address_line_1' => 'Green str, 67',
                        'address_line_2' => '',
                    ],
                    'cart_items' => [
                        [
                            'id' => $quoteItems[0]->getProduct()->getId(),
                            'quantity' => 2,
                            'title' => 'Simple Product',
                            'product_title' => 'Simple Product',
                            'weight' => $quoteItemHasWeight ? 2495 : 0,
                            'taxable' => true,
                            'image' => $productImageUrlBuilder->getUrl('no_selection', 'product_thumbnail_image'),
                            'requires_shipping' => true,
                            'line_item_key' => $quoteItems[0]->getItemId(),
                            'price' => 1000,
                            'sku' => 'simple',
                            'vendor' => '',
                        ],
                    ],
                    'taxes' => [
                    ],
                    'discounts' => [
                    ],
                    'fees' => [
                    ],
                    'shipping_line' => [
                        'rate_name' => 'Flat Rate - Fixed',
                        'cost' => 0,
                    ],
                    'totals' => [
                        'sub_total' => 2000,
                        'tax_total' => 0,
                        'discount_total' => 0,
                        'shipping_total' => 0,
                        'order_total' => 2000,
                    ],
                    'shipping_address' => [
                        'id' => null,
                        'business_name' => 'CompanyName',
                        'country_code' => 'US',
                        'country' => 'United States',
                        'city' => 'CityM',
                        'first_name' => 'John',
                        'last_name' => 'Smith',
                        'phone_number' => '3468676',
                        'postal_code' => '75477',
                        'province' => 'Alabama',
                        'province_code' => 'AL',
                        'address_line_1' => 'Green str, 67',
                        'address_line_2' => '',
                    ],
                    'customer' => [
                        'platform_id' => '1',
                        'first_name' => 'John',
                        'last_name' => 'Smith',
                        'email_address' => 'aaa@aaa.com',
                    ],
                ]
            )
            ->willReturn($boldCheckoutApiResult);

        $httpClientMock->method('getStatus')
            ->willReturn(201);
        $httpClientMock->method('getBody')
            ->willReturn(
                <<<JSON
                    {
                        "status": 201,
                        "errors": [],
                        "body": []
                    }
                JSON
            );

        if ($quoteItemHasWeight) {
            $this->addWeightToFirstQuoteItem($quote);
        }

        $hydratedOrderFromQuote->hydrate($quote, $publicOrderId);
    }

    /**
     * @return array<string, array{
     *     quoteItemHasWeight: boolean
     * }>
     */
    public function hydratesOrderFromQuoteSuccessfullyDataProvider(): array
    {
        return [
            'quote item has weight' => [
                'quoteItemHasWeight' => true,
            ],
            'quote item does not have weight' => [
                'quoteItemHasWeight' => false,
            ]
        ];
    }

    protected function tearDown(): void
    {
        $this->quote = null;
    }

    private function getQuote(): CartInterface
    {
        if ($this->quote !== null) {
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

    private function addWeightToFirstQuoteItem(CartInterface $quote): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $quoteItemRepository = $objectManager->create(CartItemRepositoryInterface::class);
        /** @var CartItemInterface[] $quoteItems */
        $quoteItems = $quote->getItems();
        /** @var CartItemInterface $firstQuoteItem */
        $firstQuoteItem = reset($quoteItems);

        $firstQuoteItem->setWeight(5.5);

        $quoteItemRepository->save($firstQuoteItem);
    }
}
