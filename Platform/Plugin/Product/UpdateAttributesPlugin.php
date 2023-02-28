<?php
declare(strict_types=1);

namespace Bold\Platform\Plugin\Product;

use Bold\Checkout\Model\ConfigInterface;
use Bold\Platform\Model\Queue\Publisher\EntitySyncPublisher;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Add updated Products ids to Bold Products synchronization queue.
 */
class UpdateAttributesPlugin
{
    public const TOPIC_NAME = 'bold.checkout.sync.products';

    /**
     * @var ConfigInterface
     */
    private $config;

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
     * @param ConfigInterface $config
     * @param EntitySyncPublisher $publisher
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ConfigInterface $config,
        EntitySyncPublisher $publisher,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->config = $config;
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
        array $productIds,
        array $attrData,
        $storeId
    ): Action {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        if ($this->config->isCheckoutEnabled($websiteId)) {
            return $result;
        }
        $intIds = array_map('intval', $productIds);
        try {
            $this->publisher->publish(self::TOPIC_NAME, $websiteId, ProductInterface::class, $intIds);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $result;
    }
}
