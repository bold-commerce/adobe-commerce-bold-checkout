<?php

declare(strict_types=1);

namespace Bold\Platform\Model\Import;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Magento\Customer\Model\Address\Validator\Postcode;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Indexer\Processor;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory;
use Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites as CountryWithWebsitesSource;
use Magento\CustomerImportExport\Model\ResourceModel\Import\Address\Storage as AddressStorage;
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
     * @var \Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher $publisher
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\ImportExport\Model\Export\Factory $collectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\Customer\StorageFactory $storageFactory
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory $attributesFactory
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Model\Address\Validator\Postcode $postcodeValidator
     * @param array $data
     * @param \Magento\Customer\Model\ResourceModel\Address\Attribute\Source\CountryWithWebsites|null $countryWithWebsites
     * @param \Magento\CustomerImportExport\Model\ResourceModel\Import\Address\Storage|null $addressStorage
     * @param \Magento\Customer\Model\Indexer\Processor|null $indexerProcessor
     */
    public function __construct(
        EntitySyncPublisher                                             $publisher,
        LoggerInterface                                                 $logger,
        StringUtils                                                     $string,
        ScopeConfigInterface                                            $scopeConfig,
        ImportFactory                                                   $importFactory,
        Helper                                                          $resourceHelper,
        ResourceConnection                                              $resource,
        ProcessingErrorAggregatorInterface                              $errorAggregator,
        StoreManagerInterface                                           $storeManager,
        Factory                                                         $collectionFactory,
        Config                                                          $eavConfig, StorageFactory $storageFactory,
        AddressFactory                                                  $addressFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionColFactory,
        CustomerFactory                                                 $customerFactory,
        CollectionFactory                                               $attributesFactory, DateTime $dateTime,
        Postcode                                                        $postcodeValidator,
        array                                                           $data = [],
        ?CountryWithWebsitesSource                                      $countryWithWebsites = null,
        ?AddressStorage                                                 $addressStorage = null,
        ?Processor                                                      $indexerProcessor = null
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
            $postcodeValidator,
            $data,
            $countryWithWebsites,
            $addressStorage,
            $indexerProcessor
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
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
