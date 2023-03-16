<?php
declare(strict_types=1);

namespace Bold\Platform\Model\Request\Validator;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Validate shop id against given store id.
 */
class ShopIdValidator
{
    /**
     * @var ConfigInterface
     */
    private $checkoutConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ConfigInterface $checkoutConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ConfigInterface $checkoutConfig, StoreManagerInterface $storeManager)
    {
        $this->checkoutConfig = $checkoutConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Verify if shop id belongs to given store.
     *
     * @param string $shopId
     * @param int $storeId
     * @return void
     * @throws LocalizedException
     */
    public function validate(string $shopId, int $storeId): void
    {
        $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        $shopIdentifier = $this->checkoutConfig->getShopIdentifier($websiteId);
        if ($shopIdentifier !== $shopId) {
            throw new LocalizedException(__('Shop Id "%1" is incorrect.', $shopId));
        }
    }
}
