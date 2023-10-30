<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\LoadAndValidate;

use Bold\Checkout\Model\ResourceModel\Quote\GetStoreIdByCartId;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Resolve store id by cart id.
 */
class StoreIdResolver
{

    /**
     * @var GetStoreIdByCartId
     */
    private $getStoreIdByCartId;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param GetStoreIdByCartId $getStoreIdByCartId
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(GetStoreIdByCartId $getStoreIdByCartId, StoreManagerInterface $storeManager)
    {
        $this->getStoreIdByCartId = $getStoreIdByCartId;
        $this->storeManager = $storeManager;
    }

    /**
     * Get store id by cart id and set current store.
     *
     * @param int $cartId
     * @return int
     */
    public function resolve(int $cartId): int
    {
        $storeId = $this->getStoreIdByCartId->getStoreId($cartId);
        $this->storeManager->setCurrentStore($storeId);
        return $storeId;
    }
}
