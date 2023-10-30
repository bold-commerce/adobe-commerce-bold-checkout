<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\LoadAndValidate;

use Bold\Checkout\Model\ResourceModel\Quote\GetStoreIdByCartId;

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
     * @param GetStoreIdByCartId $getStoreIdByCartId
     */
    public function __construct(GetStoreIdByCartId $getStoreIdByCartId)
    {
        $this->getStoreIdByCartId = $getStoreIdByCartId;
    }

    /**
     * Get store id by cart id.
     *
     * @param int $cartId
     * @return int
     */
    public function resolve(int $cartId): int
    {
        return $this->getStoreIdByCartId->getStoreId($cartId);
    }
}
