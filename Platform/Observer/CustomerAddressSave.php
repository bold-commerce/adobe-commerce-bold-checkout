<?php
declare(strict_types=1);

namespace Bold\Platform\Observer;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Publish customer ids for sync observer.
 */
class CustomerAddressSave implements ObserverInterface
{
    private const TOPIC_NAME = 'bold.checkout.sync.customers';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param StoreManagerInterface $storeManager
     * @param EntitySyncPublisher $publisher
     * @param CustomerRepositoryInterface $customerRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        EntitySyncPublisher $publisher,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger
    ) {
        $this->publisher = $publisher;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
    }

    /**
     * Publish customer sync message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $websiteIds = [];
        $address = $observer->getEvent()->getCustomerAddress();
        try {
            $customer = $this->customerRepository->getById($address->getCustomerId());
            if (!(int)$customer->getWebsiteId()) {
                $websites = $this->storeManager->getWebsites();
                foreach ($websites as $website) {
                    $websiteIds[] = (int)$website->getId();
                }
            }
            $websiteIds = $websiteIds ?: [(int)$customer->getWebsiteId()];
            foreach ($websiteIds as $websiteId) {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, [(int)$address->getCustomerId()]);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
