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
    public const PATH_SHOP_IDENTIFIER = 'checkout/bold_checkout_base/shop_identifier';

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
     * Get shared secret key (decrypted).
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
     * Get api token (decrypted).
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getApiToken(int $websiteId): ?string;

    /**
     * Get Bold API url.
     *
     * @param int $websiteId
     * @return string
     */
    public function getApiUrl(int $websiteId): string;

    /**
     * Get Bold Checkout url.
     *
     * @param int $websiteId
     * @return string
     */
    public function getCheckoutUrl(int $websiteId): string;

    /**
     * Retrieve Bold shop identifier.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getShopIdentifier(int $websiteId): ?string;

    /**
     * Set shop identifier.
     *
     * @param int $websiteId
     * @param string $shopIdentifier
     * @return void
     */
    public function setShopIdentifier(int $websiteId, string $shopIdentifier): void;
}
