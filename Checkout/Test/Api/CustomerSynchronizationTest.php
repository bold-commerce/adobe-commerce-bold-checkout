<?php

declare(strict_types=1);

namespace Bold\Checkout\Test\Api;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\BoldClient;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\QueueFactoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test Customer synchronization with Bold.
 */
class CustomerSynchronizationTest extends WebapiAbstract
{
    private const SYNC_QUEUE_TOPIC_NAME = 'bold.checkout.sync.customers';
    private const DELETE_QUEUE_TOPIC_NAME = 'bold.checkout.delete.customers';
    private const SERVICE_NAME = 'customerCustomerRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/customers';
    private const BOLD_RESOURCE_PATH = 'customers/v2/shops/{{shopId}}/customers/pid/%d';
    private const BOLD_CUSTOMER_UPDATE_DELAY = 15;

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
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * Check if Customer is correctly synchronized after update.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/customer.php
     * @magentoConfigFixture base_website checkout/bold_checkout_base/enabled 1
     */
    public function testUpdateCustomer(): void
    {
        $customer = $this->customerRepository->get('TestCustomer@example.com');
        $this->updateCustomer(['id' => $customer->getId()]);
        $updatedAttributeName = 'firstname';
        $updatedAttributeValue = 'Jim';
        $this->updateCustomer(['id' => $customer->getId(), $updatedAttributeName => $updatedAttributeValue]);
        $website = $this->storeManager->getWebsite('base');
        $url = sprintf(self::BOLD_RESOURCE_PATH, $customer->getId());
        $response = $this->boldClient->get((int)$website->getId(), $url);
        $this->assertEquals(200, $response->getStatus(), 'Customer record on Bold not found');
        $boldAttributeValue = $response->getBody()['data']['first_name'] ?? null;
        $this->assertNotNull(
            $boldAttributeValue,
            sprintf(
                'Customer record on Bold contains contains no "%s" attribute.',
                $updatedAttributeName
            )
        );
        $this->assertEquals(
            $updatedAttributeValue,
            $boldAttributeValue,
            sprintf(
                'Customer record on Bold contains contains incorrect "%s" attribute.',
                $updatedAttributeName
            )
        );
    }

    /**
     * Save Customer via Magento API.
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateCustomer(array $data): void
    {
        $this->rejectMessages();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH  . '/' . $data['id'],
                'httpMethod' => Request::HTTP_METHOD_PUT,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];
        $this->_webApiCall(
            $serviceInfo,
            ['customer' => $data]
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
     * Run consumer to synchronize Customer with Bold.
     *
     * @param string $topicName
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function runConsumers(string $topicName): void
    {
        $consumer = $this->consumerFactory->get($topicName);
        $consumer->process(1);
        sleep(self::BOLD_CUSTOMER_UPDATE_DELAY);
    }

    /**
     * Check if Customer is correctly removed after deletion.
     *
     * @magentoApiDataFixture Bold_Platform::Test/_files/customer.php
     * @magentoConfigFixture base_website checkout/bold_checkout_base/enabled 1
     */
    public function testDeleteCustomer(): void
    {
        $customer = $this->customerRepository->get('TestCustomer@example.com');
        $this->updateCustomer(['id' => $customer->getId()]);
        $website = $this->storeManager->getWebsite('base');
        $this->deleteCustomer(['id' => $customer->getId()]);
        $url = sprintf(self::BOLD_RESOURCE_PATH, $customer->getId());
        $response = $this->boldClient->get((int)$website->getId(), $url);
        $this->assertEquals(404, $response->getStatus(), 'Customer record on Bold not removed');
    }

    /**
     * Delete Customer via Magento API.
     *
     * @param array $data
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteCustomer(array $data): void
    {
        $this->rejectMessages();
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $data['id'],
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
        $this->customerRepository =  Bootstrap::getObjectManager()->get(CustomerRepositoryInterface::class);
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
