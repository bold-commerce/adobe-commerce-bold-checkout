<?php
declare(strict_types=1);

namespace Bold\Platform\Test\Api;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\BoldClient;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\MessageQueue\DefaultValueProvider;
use Magento\Framework\MessageQueue\QueueFactoryInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Test Category CRUD synchronization with Bold.
 */
class CategorySynchronizationTest extends WebapiAbstract
{
    private const SYNC_QUEUE_TOPIC_NAME = 'bold.checkout.sync.categories';
    private const DELETE_QUEUE_TOPIC_NAME = 'bold.checkout.delete.categories';
    private const SERVICE_NAME = 'catalogCategoryRepositoryV1';
    private const SERVICE_VERSION = 'V1';
    private const RESOURCE_PATH = '/V1/categories';
    private const BOLD_RESOURCE_PATH = 'products/v2/shops/{{shopId}}/categories/pid/%d';
    private const BOLD_PRODUCT_UPDATE_DELAY = 15;
    private const CATEGORY_NAME = 'Bold API-Test Category 1';
    private const UPDATED_CATEGORY_NAME = 'Bold API-Test Category 1 Updated';

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
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->defaultValueProvider = Bootstrap::getObjectManager()->get(DefaultValueProvider::class);
        $this->consumerFactory = Bootstrap::getObjectManager()->get(ConsumerFactory::class);
        $this->queueFactory = Bootstrap::getObjectManager()->get(QueueFactoryInterface::class);
        $this->boldClient = Bootstrap::getObjectManager()->get(BoldClient::class);
        $this->storeManager = Bootstrap::getObjectManager()->get(StoreManagerInterface::class);
        $this->config = Bootstrap::getObjectManager()->get(ConfigInterface::class);
        $this->categoryCollectionFactory = Bootstrap::getObjectManager()->get(CollectionFactory::class);
        $this->checkApiToken();
    }

    /**
     * @inheirtDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $collection = $this->categoryCollectionFactory->create();
        $category = $collection->addAttributeToFilter('name', self::CATEGORY_NAME)->getFirstItem();
        if (!$category->getId()) {
            $collection = $this->categoryCollectionFactory->create();
            $category = $collection->addAttributeToFilter('name', self::UPDATED_CATEGORY_NAME)->getFirstItem();
            if (!$category->getId()) {
                return;
            }
            return;
        }
        $this->deleteCategory((int)$category->getId());
    }

    /**
     * Test category will be sync with Bold on create and delete operations.
     *
     * @return void
     * @throws LocalizedException
     */
    public function testCreateUpdateDeleteCategory(): void
    {
        $this->rejectMessages();
        $category = $this->createCategory();
        $url = sprintf(self::BOLD_RESOURCE_PATH, $category['id']);
        $website = $this->storeManager->getWebsite('base');
        $response = $this->boldClient->get((int)$website->getId(), $url);
        $this->assertEquals(200, $response->getStatus(), 'Category has not been sync on create.');
        $boldCategoryName = $response->getBody()['data']['name'] ?? null;
        $this->assertEquals(self::CATEGORY_NAME, $boldCategoryName, 'Wrong category name after create.');
        $this->updateCategory(
            [
                'id' => $category['id'],
                'name' => self::UPDATED_CATEGORY_NAME,
            ]
        );
        $response = $this->boldClient->get((int)$website->getId(), $url);
        $boldCategoryName = $response->getBody()['data']['name'] ?? null;
        $this->assertEquals(self::CATEGORY_NAME, $boldCategoryName, 'Wrong category name after update.');
        $this->deleteCategory((int)$category['id']);
        $response = $this->boldClient->get((int)$website->getId(), $url);
        $this->assertEquals(404, $response->getStatus(), 'Category has not been sync on delete');
    }

    /**
     * Create test category via api.
     *
     * @return array
     * @throws LocalizedException
     */
    private function createCategory(): array
    {
        $categoryData = [
            'parent_id' => 2,
            'name' => self::CATEGORY_NAME,
            'is_active' => true,
            'position' => 1,
            'level' => 2,
            'available_sort_by' => [
                'position',
            ],
            'include_in_menu' => true,
        ];
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
        $category = $this->_webApiCall(
            $serviceInfo,
            ['category' => $categoryData]
        );
        $this->runConsumers(self::SYNC_QUEUE_TOPIC_NAME);
        return $category;
    }

    /**
     * Save Product via Magento API.
     *
     * @param array $data
     * @return void
     */
    private function updateCategory(array $data): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $data['id'],
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
            ['category' => $data]
        );
        $this->runConsumers(self::SYNC_QUEUE_TOPIC_NAME);
    }

    /**
     * Delete test category va api.
     *
     * @param int $categoryId
     * @return void
     */
    private function deleteCategory(int $categoryId): void
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH . '/' . $categoryId,
                'httpMethod' => Request::HTTP_METHOD_DELETE,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'DeleteByIdentifier',
            ],
        ];
        $this->_webApiCall($serviceInfo, ['categoryId' => $categoryId]);
        $this->runConsumers(self::DELETE_QUEUE_TOPIC_NAME);
    }

    /**
     * Run consumer to synchronize product with Bold.
     *
     * @param string $topicName
     * @return void
     * @throws LocalizedException
     */
    private function runConsumers(string $topicName): void
    {
        $consumer = $this->consumerFactory->get($topicName);
        $consumer->process(1);
        sleep(self::BOLD_PRODUCT_UPDATE_DELAY);
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
     * Check Bold API Token is set in configuration.
     *
     * @return void
     * @throws LocalizedException
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
