<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Resource;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

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
     * @param ResourceConnection $connection
     */
    public function __construct(ResourceConnection $connection)
    {
        $this->connection = $connection;
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
            ->where('scope = ?', ScopeInterface::SCOPE_WEBSITES)
            ->where('value = ?', $shopId);
        $websiteId = (int)$this->connection->getConnection()->fetchOne($select);
        if (!$websiteId) {
            throw new LocalizedException(__('No website found for "%1" shop Id.', $shopId));
        }

        return $websiteId;
    }
}
