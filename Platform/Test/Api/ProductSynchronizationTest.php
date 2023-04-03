<?php

declare(strict_types=1);

namespace Bold\Platform\Test\Api;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\BoldClient;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\QueueFactoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test Product synchronization with Bold.
 */
class ProductSynchronizationTest extends WebapiAbstract
{
    private const SYNC_QUEUE_TOPIC_NAME = 'bold.checkout.sync.products';
    private const DELETE_QUEUE_TOPIC_NAME = 'bold.checkout.delete.products';
    private const SERVICE_NAME = 'catalogProductRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/products';
    private const BOLD_RESOURCE_PATH = 'products/v2/shops/{{shopId}}/products/pid/%d';
    private const BOLD_PRODUCT_UPDATE_DELAY = 1;

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
     * @var ConfigInterface
     */
    private $config;

    /**
     * Check if Product is correctly synchronized after update.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/products.php
     * @magentoConfigFixture base_website checkout/bold_checkout_base/enabled 1
     */
    public function testUpdateProduct(): void
    {
        $this->updateProduct(['sku' => 'sample_simple_product']);
        $updatedAttributeName = 'name';
        $updatedAttributeValue = 'Updated Simple Product';
        $this->updateProduct(['sku' => 'sample_simple_product', $updatedAttributeName => $updatedAttributeValue]);
        $website = $this->storeManager->getWebsite('base');
        $product = $this->productRepository->get('sample_simple_product');
        $url = sprintf(self::BOLD_RESOURCE_PATH, $product->getId());
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
     * Save Product via Magento API.
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateProduct(array $data): void
    {
        $this->rejectMessages();
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
        $this->_webApiCall(
            $serviceInfo,
            ['product' => $data]
        );
        $this->runConsumers(self::SYNC_QUEUE_TOPIC_NAME);
    }

    /**
     * Reject all previously created queue messages.
     *
     * @return void
     */
    private function rejectMessages()
    {
        $queues = [
            self::SYNC_QUEUE_TOPIC_NAME,
            self::DELETE_QUEUE_TOPIC_NAME,
        ];
        foreach ($queues as $queueName) {
            $queue = $this->queueFactory->create(
                $queueName,
                $this->defaultValueProvider->getConnection()
            );
            while ($envelope = $queue->dequeue()) {
                $queue->reject($envelope, false);
            }
        }
    }

    /**
     * Run consumer to synchronize product with Bold.
     *
     * @param string $topicName
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function runConsumers(string $topicName): void
    {
        $consumer = $this->consumerFactory->get($topicName);
        $consumer->process(1);
        sleep(self::BOLD_PRODUCT_UPDATE_DELAY);
    }

    /**
     * Check if Product is correctly removed after deletion.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/products.php
     * @magentoConfigFixture base_website checkout/bold_checkout_base/enabled 1
     */
    public function testDeleteProduct(): void
    {
        $this->updateProduct(['sku' => 'sample_simple_product']);
        $website = $this->storeManager->getWebsite('base');
        $product = $this->productRepository->get('sample_simple_product');
        $this->deleteProduct(['sku' => 'sample_simple_product']);
        $url = sprintf(self::BOLD_RESOURCE_PATH, $product->getId());
        $response = $this->boldClient->call((int)$website->getId(), 'GET', $url, []);
        $this->assertEquals(404, $response->getStatus(), 'Product record on Bold not removed');
    }

    /**
     * Delete Product via Magento API.
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteProduct(array $data): void
    {
        $this->rejectMessages();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $data['sku'],
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'deleteById',
            ],
        ];
        $this->_webApiCall(
            $serviceInfo,
            $data
        );
        $this->runConsumers(self::DELETE_QUEUE_TOPIC_NAME);
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
        $this->config = Bootstrap::getObjectManager()->get(ConfigInterface::class);

        $this->checkApiToken();
    }

    /**
     * Check Bold API Token is set in configuration.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkApiToken(): void
    {
        $website = $this->storeManager->getWebsite('base');
        $this->assertNotEmpty(
            $this->config->getApiToken((int)$website->getId()),
            'Bold API Token is not set.'
        );
    }
}
