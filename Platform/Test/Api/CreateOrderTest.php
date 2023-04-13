<?php
declare(strict_types=1);

namespace Bold\Platform\Test\Api;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Reports\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory as InvoiceCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Covers bold order create endpoint.
 */
class CreateOrderTest extends WebapiAbstract
{
    private const SERVICE_NAME = 'boldPlatformPlaceOrderV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/shops/{{shopId}}/orders';
    private const QUOTE_RESERVED_ORDER_ID = 'bold_test_reserved_order_id';

    /**
     * @var CartInterface|null
     */
    private $quote;

    /**
     * @inheirtDoc
     */
    protected function setUp(): void
    {
        $this->quote = $this->getActiveQuote();
    }

    /**
     * @inheirtDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->quote->setIsActive(0);
        $cartRepository = Bootstrap::getObjectManager()->get(CartRepositoryInterface::class);
        $cartRepository->save($this->quote);
    }

    /**
     * Test place order for guest customer with delayed capture.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/guest_quote.php
     * @return void
     */
    public function testGuestCustomerPlaceOrderDelayedCapture(): void
    {
        self::_markTestAsRestOnly();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');
        $config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $shopId = $config->getShopId((int)$website->getId());
        $orderNumber = Bootstrap::getObjectManager()->get(Random::class)->getRandomString(10);
        $orderRequestBody = $this->getPlaceOrderPayload($orderNumber);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', $shopId, self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $orderRequestBody);
        $this->verifyCreatedOrderData($orderNumber, $result['order']);
        self::assertEquals(1, $result['order']['customer_is_guest']);
    }

    /**
     * Test place order for guest customer with delayed capture.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/customer_quote.php
     * @return void
     */
    public function testCustomerPlaceOrderDelayedCapture(): void
    {
        self::_markTestAsRestOnly();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');
        $config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $shopId = $config->getShopId((int)$website->getId());
        $orderNumber = Bootstrap::getObjectManager()->get(Random::class)->getRandomString(10);
        $orderRequestBody = $this->getPlaceOrderPayload($orderNumber);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', $shopId, self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $orderRequestBody);
        $this->verifyCreatedOrderData($orderNumber, $result['order']);
        self::assertEquals('customer@example.com', $result['order']['customer_email']);
        self::assertEquals(0, $result['order']['customer_is_guest']);
    }

    /**
     * Test place order for guest customer with immediate capture.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/guest_quote.php
     * @return void
     */
    public function testGuestCustomerPlaceOrderImmediateCapture(): void
    {
        self::_markTestAsRestOnly();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');
        $config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $shopId = $config->getShopId((int)$website->getId());
        $orderNumber = Bootstrap::getObjectManager()->get(Random::class)->getRandomString(10);
        $orderRequestBody = $this->getPlaceOrderPayload($orderNumber, false);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', $shopId, self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $orderRequestBody);
        $this->verifyCreatedOrderData($orderNumber, $result['order'], false);
        self::assertEquals(1, $result['order']['customer_is_guest']);
    }

    /**
     * Test place order for guest customer with immediate capture.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/customer_quote.php
     * @return void
     */
    public function testCustomerPlaceOrderImmediateCapture(): void
    {
        self::_markTestAsRestOnly();
        $storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $website = $storeManager->getWebsite('base');
        $config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $shopId = $config->getShopId((int)$website->getId());
        $orderNumber = Bootstrap::getObjectManager()->get(Random::class)->getRandomString(10);
        $orderRequestBody = $this->getPlaceOrderPayload($orderNumber, false);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => str_replace('{{shopId}}', $shopId, self::RESOURCE_PATH),
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
        ];
        $result = $this->_webApiCall($serviceInfo, $orderRequestBody);
        $this->verifyCreatedOrderData($orderNumber, $result['order'], false);
        self::assertEquals(0, $result['order']['customer_is_guest']);
        self::assertEquals('customer@example.com', $result['order']['customer_email']);
    }

    /**
     * Build order place payload.
     *
     * @param string $orderNumber
     * @param bool $delayedCapture
     * @return array[]
     */
    private function getPlaceOrderPayload(string $orderNumber, bool $delayedCapture = true): array
    {
        return [
            'order' => [
                'quoteId' => $this->quote->getId(),
                'billingAddress' => [
                    'city' => 'Culver City',
                    'company' => '',
                    'country_id' => 'US',
                    'email' => 'customer@example.com',
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'telephone' => '555-55-555-55',
                    'postcode' => '90230',
                    'region' => 'California',
                    'region_code' => 'CA',
                    'street' => [
                        'West Centinela Avenue',
                    ],
                ],
                'browserIp' => '',
                'publicId' => 'test_bold_order_public_id',
                'financialStatus' => 'pending',
                'fulfillmentStatus' => 'pending',
                'orderNumber' => $orderNumber,
                'orderStatus' => 'test_order_status',
                'payment' => [
                    'base_amount_ordered' => '100',
                    'base_amount_authorized' => '100',
                    'base_amount_paid' => $delayedCapture ? '0' : '100',
                    'last_trans_id' => 'ch_3M816MB5K8kykDdS00gmtLOO',
                    'method' => 'credit',
                ],
                'transaction' => [
                    'txn_id' => 'ch_3M816MB5K8kykDdS00gmtLOO',
                    'txn_type' => $delayedCapture ? 'authorization' : 'capture',
                ],
                'total' => '100',
            ],
        ];
    }

    /**
     * Retrieve active quote created by quote fixture.
     *
     * @return CartInterface
     */
    private function getActiveQuote(): CartInterface
    {
        $quoteCollection = Bootstrap::getObjectManager()->get(CollectionFactory::class)->create();
        $quoteCollection->addFieldToFilter('reserved_order_id', ['eq' => self::QUOTE_RESERVED_ORDER_ID]);
        $quoteCollection->addFieldToFilter('is_active', ['eq' => 1]);
        return $quoteCollection->getFirstItem();
    }

    /**
     * Verify created order payload.
     *
     * @param CartInterface $quote
     * @param string $orderNumber
     * @param array $order
     * @param bool $delayedCapture
     * @return void
     */
    private function verifyCreatedOrderData(
        string $orderNumber,
        array $order,
        bool $delayedCapture = true
    ): void {
        $state = $delayedCapture ? 'new' : 'processing';
        $status = $delayedCapture ? 'pending' : 'processing';
        if (!$delayedCapture) {
            $invoiceCollection = Bootstrap::getObjectManager()->get(InvoiceCollectionFactory::class)->create();
            $invoiceCollection->addFieldToFilter(
                'order_id',
                ['eq' => $order['entity_id']]
            );
            $invoice = $invoiceCollection->getFirstItem();
            self::assertEquals((float)$invoice->getBaseGrandTotal(), $order['base_grand_total']);
            self::assertEquals((float)$invoice->getGrandTotal(), $order['grand_total']);
            self::assertEquals('ch_3M816MB5K8kykDdS00gmtLOO', $invoice->getTransactionId());
        }
        self::assertEquals((float)$this->quote->getBaseGrandTotal(), $order['base_grand_total']);
        self::assertEquals((float)$this->quote->getGrandTotal(), $order['grand_total']);
        self::assertEquals($orderNumber, $order['ext_order_id']);
        self::assertEquals($state, $order['state']);
        self::assertEquals($status, $order['status']);
        self::assertEquals('bold', $order['payment']['method']);
        self::assertEquals('Bold Payments', $order['payment']['additional_information'][0]);
        self::assertEquals('ch_3M816MB5K8kykDdS00gmtLOO', $order['payment']['last_trans_id']);
        self::assertEquals('customer@example.com', $order['billing_address']['email']);
        self::assertEquals('Culver City', $order['billing_address']['city']);
        self::assertEquals('US', $order['billing_address']['country_id']);
        self::assertEquals('California', $order['billing_address']['region']);
        self::assertEquals('90230', $order['billing_address']['postcode']);
        $comment = $delayedCapture
            ? 'Please consider to cancel this order due to it\'s total = ' . $order['grand_total'] .
            ' mismatch authorization transaction amount = 100. For more details please refer to Bold Help Center' .
            ' at "https://support.boldcommerce.com"'
            : 'Please consider to refund this order due to it\'s total = ' . $order['grand_total'] .
            ' mismatch payment transaction amount = 100. For more details please refer to Bold Help Center' .
            ' at "https://support.boldcommerce.com"';
        self::assertEquals($comment, $order['status_histories'][0]['comment']);
    }
}
