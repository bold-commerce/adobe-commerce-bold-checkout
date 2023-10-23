<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\ResourceModel;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Retrieve website id for given shop id.
 */
class GetWebsiteIdByShopId
{
    /**
     * @var ResourceConnection
     */
    private $connection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $connection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $connection,
        StoreManagerInterface $storeManager
    ) {
        $this->connection = $connection;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve website id for given shop Id.
     *
     * @param string $shopId
     * @return int
     * @throws LocalizedException
     */
    public function getWebsiteId(string $shopId): int
    {
        $select = $this->connection->getConnection()
            ->select()
            ->from($this->connection->getTableName('core_config_data'), ['scope_id'])
            ->where('path = ?', ConfigInterface::PATH_SHOP_ID)
            ->where('value = ?', $shopId);
        $websiteId = $this->connection->getConnection()->fetchOne($select);
        if ($websiteId === false) {
            throw new LocalizedException(__('No website found for "%1" shop Id.', $shopId));
        }
        $websiteId = $websiteId ?: (int)$this->storeManager->getWebsite()->getId();

        return (int)$websiteId;
    }
}
