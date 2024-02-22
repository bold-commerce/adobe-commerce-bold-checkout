<?php

declare(strict_types=1);

namespace Bold\Checkout\Api;

/**
 * Get all installed modules and their versions.
 */
interface ClearCartInterface
{
    /**
     * Clear the current customers cart.
     *
     * @param string $shopId
     * @return array
     */
    public function clear(string $shopId): array;
}
