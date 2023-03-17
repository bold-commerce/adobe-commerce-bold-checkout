<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

/**
 * Bold config model interface.
 */
interface ConfigInterface
{
    public const VALUE_ENABLED_FOR_ALL = 0;
    public const VALUE_ENABLED_FOR_IP = 1;
    public const VALUE_ENABLED_FOR_CUSTOMER = 2;
    public const VALUE_ENABLED_FOR_PERCENTAGE = 3;
    public const PATH_SHOP_ID = 'checkout/bold_checkout_base/shop_id';

    /**
     * Check if bold functionality enabled.
     *
     * @param int $websiteId
     * @return bool
     */
    public function isCheckoutEnabled(int $websiteId): bool;

    /**
     * Show if Bold functionality is enabled for specific customers.
     *
     * @param int $websiteId
     * @return int
     */
    public function getEnabledFor(int $websiteId): int;

    /**
     * Get IP whitelist.
     *
     * @return string[]
     */
    public function getIpWhitelist(int $websiteId): array;

    /**
     * Get Customer email whitelist.
     *
     * @return string[]
     */
    public function getCustomerWhitelist(int $websiteId): array;

    /**
     * Get Orders percentage.
     *
     * @param int $websiteId
     * @return int
     */
    public function getOrdersPercentage(int $websiteId): int;

    /**
     * Get shared secret key for M2 Platform Integration.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getSharedSecret(int $websiteId): ?string;

    /**
     * Set shared secret for outgoing calls to bold m2 integration.
     *
     * @param int $websiteId
     * @param string $sharedSecret
     * @return void
     */
    public function setSharedSecret(int $websiteId, string $sharedSecret): void;

    /**
     * Get Bold Checkout Api Token.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getApiToken(int $websiteId): ?string;

    /**
     * Get M2 Platform connector API url.
     *
     * @param int $websiteId
     * @return string
     */
    public function getPlatformConnectorUrl(int $websiteId): string;

    /**
     * Retrieve Bold shop identifier.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getShopId(int $websiteId): ?string;

    /**
     * Set shop identifier.
     *
     * @param int $websiteId
     * @param string $shopId
     * @return void
     */
    public function setShopId(int $websiteId, string $shopId): void;
}
