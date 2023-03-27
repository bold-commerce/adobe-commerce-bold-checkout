<?php

declare(strict_types=1);

namespace Bold\Platform\Test\Api;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Http\BoldClient;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\QueueFactoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ProductUpdateTest extends WebapiAbstract
{
    private const QUEUE_TOPIC_NAME = 'bold.checkout.sync.products';
    private const SERVICE_NAME = 'catalogProductRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products';

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var DefaultValueProvider
     */
    private $defaultValueProvider;

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @var QueueFactoryInterface
     */
    private $queueFactory;

    /**
     * @var ClientInterface
     */
    private $boldClient;

    /**
     * @var BoldClient
     */
    private $storeManager;

    /**
     * Check if Product is correctly synchronized after update.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/products.php
     *
     * @magentoConfigFixture base_website checkout/bold_checkout_base/enabled 1
     * TODO: set key securely
     * @magentoConfigFixture base_website checkout/bold_checkout_base/api_token key
     */
    public function testUpdateProductSku(): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $updatedAttributeName = 'name';
        $updatedAttributeValue = 'Updated Simple Product';
        $website = $this->storeManager->getWebsite('base');
        $product = $this->productRepository->get('sample_simple_product');
        $this->_webApiCall(
            $serviceInfo,
            ['product' => ['sku' => 'sample_simple_product', $updatedAttributeName => $updatedAttributeValue]]
        );
        $this->runConsumers();
        $url = 'products/v2/shops/{{shopId}}/products/pid/' . $product->getId();
        $response = $this->boldClient->call((int)$website->getId(), 'GET', $url, []);
        $this->assertEquals(200, $response->getStatus(), 'Product record on Bold not found');
        $boldAttributeValue = $response->getBody()['data'][$updatedAttributeName] ?? null;
        $this->assertNotNull(
            $boldAttributeValue,
            sprintf(
                'Product record on Bold contains contains no "%s" attribute.',
                $updatedAttributeName
            )
        );
        $this->assertEquals(
            $updatedAttributeValue,
            $boldAttributeValue,
            sprintf(
                'Product record on Bold contains contains incorrect "%s" attribute.',
                $updatedAttributeName
            )
        );
    }

    /**
     * Run consumer to synchronize product with Bold.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function runConsumers(): void
    {
        $consumer = $this->consumerFactory->get(self::QUEUE_TOPIC_NAME);
        $consumer->process(1);
        sleep(1);
    }

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->productRepository = Bootstrap::getObjectManager()->get(ProductRepositoryInterface::class);
        $this->defaultValueProvider = Bootstrap::getObjectManager()->get(DefaultValueProvider::class);
        $this->consumerFactory = Bootstrap::getObjectManager()->get(ConsumerFactory::class);
        $this->queueFactory = Bootstrap::getObjectManager()->get(QueueFactoryInterface::class);
        $this->boldClient = Bootstrap::getObjectManager()->get(BoldClient::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);

        $this->rejectMessages();
    }

    /**
     * Reject all previously created queue messages.
     *
     * @return void
     */
    private function rejectMessages()
    {
        $queue = $this->queueFactory->create(
            self::QUEUE_TOPIC_NAME,
            $this->defaultValueProvider->getConnection()
        );
        while ($envelope = $queue->dequeue()) {
            $queue->reject($envelope, false);
        }
    }
}
