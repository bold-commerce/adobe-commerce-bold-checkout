<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Import;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add Customer ids to Bold synchronization / deletion queues on Customer import.
 */
class Customer extends \Magento\CustomerImportExport\Model\Import\Customer
{
    private const SYNC_TOPIC_NAME = 'bold.checkout.sync.customers';
    private const DELETE_TOPIC_NAME = 'bold.checkout.delete.customers';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntitySyncPublisher $publisher
     * @param LoggerInterface $logger
     * @param StringUtils $string
     * @param ScopeConfigInterface $scopeConfig
     * @param ImportFactory $importFactory
     * @param Helper $resourceHelper
     * @param ResourceConnection $resource
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param StoreManagerInterface $storeManager
     * @param Factory $collectionFactory
     * @param Config $eavConfig
     * @param StorageFactory $storageFactory
     * @param CollectionFactory $attrCollectionFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        EntitySyncPublisher $publisher,
        LoggerInterface $logger,
        StringUtils $string,
        ScopeConfigInterface $scopeConfig,
        ImportFactory $importFactory,
        Helper $resourceHelper,
        ResourceConnection $resource,
        ProcessingErrorAggregatorInterface $errorAggregator,
        StoreManagerInterface $storeManager,
        Factory $collectionFactory,
        Config $eavConfig,
        StorageFactory $storageFactory,
        CollectionFactory $attrCollectionFactory,
        CustomerFactory $customerFactory
    ) {
        parent::__construct(
            $string,
            $scopeConfig,
            $importFactory,
            $resourceHelper,
            $resource,
            $errorAggregator,
            $storeManager,
            $collectionFactory,
            $eavConfig,
            $storageFactory,
            $attrCollectionFactory,
            $customerFactory
        );
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function _saveCustomerEntities(array $entitiesToCreate, array $entitiesToUpdate)
    {
        $result = parent::_saveCustomerEntities($entitiesToCreate, $entitiesToUpdate);
        $customerIds = array_map(
            function (array $customerData) {
                return (int)$customerData['entity_id'];
            },
            array_merge($entitiesToCreate, $entitiesToUpdate)
        );
        foreach ($this->getWebsiteIds() as $websiteId) {
            $this->addToQueue(self::SYNC_TOPIC_NAME, $websiteId, $customerIds);
        }

        return $result;
    }

    /**
     * Get all website ids.
     *
     * @return array
     */
    private function getWebsiteIds(): array
    {
        return array_map(
            function (WebsiteInterface $website) {
                return (int)$website->getId();
            },
            $this->_storeManager->getWebsites()
        );
    }

    /**
     * Add customer ids to queue.
     *
     * @param string $topicName
     * @param int $websiteId
     * @param int[] $entityIds
     * @return void
     */
    private function addToQueue(string $topicName, int $websiteId, array $entityIds): void
    {
        if (!$websiteId || empty($entityIds)) {
            return;
        }
        try {
            $this->publisher->publish($topicName, $websiteId, $entityIds);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @inheritDoc
     */
    protected function _saveCustomerAttributes(array $attributesData)
    {
        $result = parent::_saveCustomerAttributes($attributesData);
        $customerIds =
            array_map('intval',
                array_unique(
                    array_reduce(
                        $attributesData,
                        function ($carry, $attributeData) {
                            return array_merge($carry, array_keys($attributeData));
                        },
                        []
                    )
                )
            );
        foreach ($this->getWebsiteIds() as $websiteId) {
            $this->addToQueue(self::SYNC_TOPIC_NAME, $websiteId, $customerIds);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function _deleteCustomerEntities(array $entitiesToDelete)
    {
        $result = parent::_deleteCustomerEntities($entitiesToDelete);
        foreach ($this->getWebsiteIds() as $websiteId) {
            $this->addToQueue(self::DELETE_TOPIC_NAME, $websiteId, array_map('intval', $entitiesToDelete));
        }

        return $result;
    }
}
