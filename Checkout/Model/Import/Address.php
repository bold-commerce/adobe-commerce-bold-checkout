<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Import;

use Bold\Checkout\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Model\Address\Validator\Postcode;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Model\Export\Factory;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ImportFactory;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add Customer ids to Bold synchronization queue on Customer Address import.
 */
class Address extends \Magento\CustomerImportExport\Model\Import\Address
{
    private const SYNC_TOPIC_NAME = 'bold.checkout.sync.customers';

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
     * @param AddressFactory $addressFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory
     * @param CustomerFactory $customerFactory
     * @param CollectionFactory $attributesFactory
     * @param DateTime $dateTime
     * @param Postcode $postcodeValidator
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
        AddressFactory $addressFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory,
        CustomerFactory $customerFactory,
        CollectionFactory $attributesFactory,
        DateTime $dateTime,
        Postcode $postcodeValidator
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
            $addressFactory,
            $regionColFactory,
            $customerFactory,
            $attributesFactory,
            $dateTime,
            $postcodeValidator
        );
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    protected function _saveAddressEntities(array $addRows, array $updateRows)
    {
        $result = parent::_saveAddressEntities($addRows, $updateRows);
        $customerIds = array_map(
            function (array $addressData) {
                return (int)$addressData['parent_id'];
            },
            array_merge($addRows, $updateRows)
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
    protected function _saveCustomerDefaults(array $defaults)
    {
        $result = parent::_saveCustomerDefaults($defaults);
        $customerIds =
            array_map('intval',
                array_unique(
                    array_reduce(
                        $defaults,
                        function ($carry, $data) {
                            return array_merge($carry, array_keys($data));
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
}
