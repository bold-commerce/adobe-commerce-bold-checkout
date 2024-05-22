<?php

declare(strict_types=1);

namespace Bold\Checkout\Test\Api;

use DMS\PHPUnitExtensions\ArraySubset\ArraySubsetAsserts;
use Magento\Framework\Flag;
use Magento\Framework\Flag\FlagResource;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

final class PlaceOrderTest extends WebapiAbstract
{
    use ArraySubsetAsserts;

    /**
     * @magentoConfigFixture checkout/bold_checkout_base/shop_id 74e51be84d1643e8a89df356b80bf2b5
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     */
    public function testAuthorizesAndPlacesOrderSuccessfully(): void
    {
        $this->_markTestAsRestOnly();

        $publicOrderId = 'd407dc80-3470-49a4-9969-7a12cf17fd4a';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/orders/$publicOrderId/quote/1/authorizeAndPlace",
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $objectManager = Bootstrap::getObjectManager();
        $flag = $objectManager->create(
            Flag::class,
            [
                'data' => [
                    'flag_code' => 'bold_api_test_data_payment_auth'
                ]
            ]
        );
        $flagResource = $objectManager->create(FlagResource::class);
        $expectedOrderData = [
            'base_currency_code' => 'USD',
            'base_discount_amount' => 0,
            'base_grand_total' => 30,
            'base_discount_tax_compensation_amount' => 0,
            'base_shipping_amount' => 10,
            'base_shipping_discount_amount' => 0,
            'base_shipping_discount_tax_compensation_amnt' => 0,
            'base_shipping_incl_tax' => 10,
            'base_shipping_tax_amount' => 0,
            'base_subtotal' => 20,
            'base_subtotal_incl_tax' => 20,
            'base_tax_amount' => 0,
            'base_total_due' => 30,
            'base_to_global_rate' => 1,
            'base_to_order_rate' => 1,
            'billing_address_id' => 2,
            'customer_email' => 'aaa@aaa.com',
            'customer_firstname' => 'John',
            'customer_id' => 1,
            'customer_is_guest' => 0,
            'customer_lastname' => 'Smith',
            'customer_middlename' => 'A',
            'customer_note_notify' => 1,
            'customer_prefix' => 'Mr.',
            'customer_suffix' => 'Esq.',
            'grand_total' => 30,
            'order_currency_code' => 'USD',
            'quote_id' => 1,
            'shipping_amount' => 10,
            'shipping_description' => 'Flat Rate - Fixed',
            'state' => 'new',
            'status' => 'pending',
            'store_id' => 1,
            'subtotal' => 20,
            'subtotal_incl_tax' => 20,
            'tax_amount' => 0,
            'total_due' => 30,
            'total_item_count' => 1,
            'total_qty_ordered' => 2,
            'items' => [
                0 => [
                    'base_discount_amount' => 0,
                    'base_discount_tax_compensation_amount' => 0,
                    'base_original_price' => 10,
                    'base_price' => 10,
                    'base_price_incl_tax' => 10,
                    'base_row_total' => 20,
                    'base_row_total_incl_tax' => 20,
                    'name' => 'Simple Product',
                    'order_id' => 1,
                    'original_price' => 10,
                    'price' => 10,
                    'price_incl_tax' => 10,
                    'product_id' => 1,
                    'product_type' => 'simple',
                    'qty_ordered' => 2,
                    'quote_item_id' => 1,
                    'row_total' => 20,
                    'row_total_incl_tax' => 20,
                    'sku' => 'simple',
                    'store_id' => 1,
                    'tax_amount' => 0,
                    'tax_percent' => 0,
                ],
            ],
            'billing_address' => [
                'address_type' => 'billing',
                'city' => 'CityM',
                'company' => 'CompanyName',
                'country_id' => 'US',
                'customer_address_id' => 1,
                'email' => 'aaa@aaa.com',
                'entity_id' => 2,
                'firstname' => 'John',
                'lastname' => 'Smith',
                'parent_id' => 1,
                'postcode' => '75477',
                'region' => 'Alabama',
                'region_code' => 'AL',
                'region_id' => 1,
                'street' => [
                    0 => 'Green str, 67',
                ],
                'telephone' => '3468676',
            ],
            'payment' => [
                'account_status' => null,
                'additional_information' => [
                    0 => 'Bold Payments',
                ],
                'amount_ordered' => 30,
                'amount_paid' => 30,
                'base_amount_ordered' => 30,
                'base_shipping_amount' => 10,
                'cc_last4' => null,
                'entity_id' => 1,
                'last_trans_id' => 'b9f35c91-1c16-4a3e-a985-a6a1af44c0ac',
                'method' => 'bold',
                'parent_id' => 1,
                'shipping_amount' => 10,
            ],
            'extension_attributes' => [
                'shipping_assignments' => [
                    0 => [
                        'shipping' => [
                            'address' => [
                                'address_type' => 'shipping',
                                'city' => 'CityM',
                                'company' => 'CompanyName',
                                'country_id' => 'US',
                                'customer_address_id' => 1,
                                'email' => 'aaa@aaa.com',
                                'entity_id' => 1,
                                'firstname' => 'John',
                                'lastname' => 'Smith',
                                'parent_id' => 1,
                                'postcode' => '75477',
                                'region' => 'Alabama',
                                'region_code' => 'AL',
                                'region_id' => 1,
                                'street' => [
                                    0 => 'Green str, 67',
                                ],
                                'telephone' => '3468676',
                            ],
                            'method' => 'flatrate_flatrate',
                            'total' => [
                                'base_shipping_amount' => 10,
                                'base_shipping_discount_amount' => 0,
                                'base_shipping_discount_tax_compensation_amnt' => 0,
                                'base_shipping_incl_tax' => 10,
                                'base_shipping_tax_amount' => 0,
                                'shipping_amount' => 10,
                                'shipping_discount_amount' => 0,
                                'shipping_discount_tax_compensation_amount' => 0,
                                'shipping_incl_tax' => 10,
                                'shipping_tax_amount' => 0,
                            ],
                        ],
                        'items' => [
                            0 => [
                                'base_original_price' => 10,
                                'base_price' => 10,
                                'base_price_incl_tax' => 10,
                                'base_row_invoiced' => 0,
                                'base_row_total' => 20,
                                'base_row_total_incl_tax' => 20,
                                'base_tax_amount' => 0,
                                'base_tax_invoiced' => 0,
                                'item_id' => 1,
                                'name' => 'Simple Product',
                                'no_discount' => 0,
                                'order_id' => 1,
                                'original_price' => 10,
                                'price' => 10,
                                'price_incl_tax' => 10,
                                'product_id' => 1,
                                'product_type' => 'simple',
                                'qty_ordered' => 2,
                                'quote_item_id' => 1,
                                'row_total' => 20,
                                'row_total_incl_tax' => 20,
                                'sku' => 'simple',
                                'store_id' => 1,
                                'tax_amount' => 0,
                                'tax_invoiced' => 0,
                                'tax_percent' => 0,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $flag->setFlagData(
            [
                'data' => [
                    'total' => 30.0000,
                    'transactions' => [
                        [
                            'gateway' => 'Test Payment Gateway',
                            'gateway_id' => 'ff2e05a2-04c7-4db3-9a3d-c15f5dcca7fe',
                            'amount' => 30.0000,
                            'transaction_id' => 'b9f35c91-1c16-4a3e-a985-a6a1af44c0ac',
                            'reference_transaction_id' => null,
                            'response_code' => '42',
                            'status' => 'success'
                        ]
                    ]
                ]
            ]
        );

        $flagResource->save($flag);

        $response = $this->_webApiCall($serviceInfo);

        $flagResource->delete($flag);

        self::assertEmpty($response['errors']);
        self::assertArraySubset($expectedOrderData, $response['order']);
    }

    /**
     * @magentoConfigFixture checkout/bold_checkout_base/shop_id 74e51be84d1643e8a89df356b80bf2b5
     */
    public function testDoesNotAuthorizeAndPlaceSuccessfullyIfQuoteIdIsInvalid(): void
    {
        $this->_markTestAsRestOnly();

        $publicOrderId = 'fe90e903-e327-4ff4-ad31-c22529e33e50';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/orders/$publicOrderId/quote/42/authorizeAndPlace",
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $expectedErrorData = [
            [
                'message' => 'Could not find quote with ID "42"',
                'code' => 422,
                'type' => 'server.validation_error'
            ]
        ];

        $response = $this->_webApiCall($serviceInfo);

        self::assertEquals($expectedErrorData, $response['errors']);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoConfigFixture checkout/bold_checkout_base/shop_id 74e51be84d1643e8a89df356b80bf2b5
     * @magentoApiDataFixture Magento/Checkout/_files/quote_with_shipping_method.php
     * @dataProvider boldAuthorizedPaymentsApiResultDataProvider
     */
    public function testDoesNotAuthorizeAndPlaceSuccessfullyIfBoldApiReturnsError(
        array $authorizedPayments,
        array $expectedErrorResponse
    ): void {
        self::markTestIncomplete('We need to figure out why the quote is not loading correctly for this test');

        $this->_markTestAsRestOnly();

        $publicOrderId = 'fe90e903-e327-4ff4-ad31-c22529e33e50';
        $serviceInfo = [
            'rest' => [
                'resourcePath' => "/V1/orders/$publicOrderId/quote/1/authorizeAndPlace",
                'httpMethod' => Request::HTTP_METHOD_GET,
            ],
        ];
        $objectManager = Bootstrap::getObjectManager();
        $flag = $objectManager->create(
            Flag::class,
            [
                'data' => [
                    'flag_code' => 'bold_api_test_data_payment_auth'
                ]
            ]
        );
        $flagResource = $objectManager->create(FlagResource::class);

        $flag->setFlagData($authorizedPayments);

        $flagResource->save($flag);

        $response = $this->_webApiCall($serviceInfo);

        $flagResource->delete($flag);

        self::assertEquals($expectedErrorResponse, $response['errors']);
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
                                'gateway_id' => 'd7fc6a17-04de-489c-a1ce-1d3571275791',
                                'amount' => 30.0000,
                                'reference_transaction_id' => null,
                                'response_code' => '123',
                                'status' => 'failure'
                            ]
                        ]
                    ]
                ],
                'expectedErrorResponse' => [
                    [
                        'code' => '123',
                        'type' => 'declined',
                        'message' => 'Payment declined for insufficient funds',
                    ]
                ]
            ],
            'failed and successful transactions' => [
                'authorizedPayments' => [
                    'data' => [
                        'total' => 30.0000,
                        'transactions' => [
                            [
                                'gateway' => 'Test Payment Gateway',
                                'gateway_id' => 'ff2e05a2-04c7-4db3-9a3d-c15f5dcca7fe',
                                'amount' => 30.0000,
                                'transaction_id' => 'b9f35c91-1c16-4a3e-a985-a6a1af44c0ac',
                                'reference_transaction_id' => null,
                                'response_code' => '42',
                                'status' => 'success'
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
                                'gateway_id' => 'd7fc6a17-04de-489c-a1ce-1d3571275791',
                                'amount' => 30.0000,
                                'reference_transaction_id' => null,
                                'response_code' => '123',
                                'status' => 'failure'
                            ]
                        ]
                    ]
                ],
                'expectedErrorResponse' => [
                    [
                        'code' => '123',
                        'type' => 'declined',
                        'message' => 'Payment declined for insufficient funds',
                    ]
                ]
            ],
        ];
    }
}
