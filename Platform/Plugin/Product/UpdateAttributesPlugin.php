<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Product;

use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add updated Products ids to Bold Products synchronization queue.
 */
class UpdateAttributesPlugin
{
    public const TOPIC_NAME = 'bold.checkout.sync.products';

    /**
     * @var EntitySyncPublisher
     */
    private $publisher;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EntitySyncPublisher $publisher
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        EntitySyncPublisher   $publisher,
        StoreManagerInterface $storeManager,
        LoggerInterface       $logger
    ) {
        $this->publisher = $publisher;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Add updated Products ids to Bold Products synchronization queue.
     *
     * @param Action $subject
     * @param Action $result
     * @param array $productIds
     * @param array $attrData
     * @param int $storeId
     * @return Action
     * @throws NoSuchEntityException
     */
    public function afterUpdateAttributes(
        Action $subject,
        Action $result,
        array  $productIds,
        array  $attrData,
               $storeId
    ): Action {
        $websiteIds = $storeId
            ? [(int)$this->storeManager->getStore($storeId)->getWebsiteId()]
            : array_map(
                function (WebsiteInterface $website) {
                    return (int)$website->getId();
                },
                $this->storeManager->getWebsites()
            );

        $intIds = array_map('intval', $productIds);
        foreach ($websiteIds as $websiteId) {
            try {
                $this->publisher->publish(self::TOPIC_NAME, $websiteId, $intIds);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $result;
    }
}
