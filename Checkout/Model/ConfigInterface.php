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

    /**
     * Check if bold functionality enabled.
     *
     * @return bool
     */
    public function isCheckoutEnabled(): bool;

    /**
     * Show if Bold functionality is enabled for specific customers.
     *
     * @return int
     */
    public function getEnabledFor(): int;

    /**
     * Get IP whitelist.
     *
     * @return string[]
     */
    public function getIpWhitelist(): array;

    /**
     * Get Customer email whitelist.
     *
     * @return string[]
     */
    public function getCustomerWhitelist(): array;

    /**
     * Get Orders percentage.
     *
     * @return int
     */
    public function getOrdersPercentage(): int;

    /**
     * Check if real-time synchronization is enabled.
     *
     * @return bool
     */
    public function isRealtimeEnabled(): bool;

    /**
     * Get shared secret key (decrypted).
     *
     * @return string|null
     */
    public function getSharedSecret(): ?string;

    /**
     * Get api token (decrypted).
     *
     * @return string|null
     */
    public function getApiToken(): ?string;

    /**
     * Get configured weight unit to grams conversion rate.
     *
     * @return float
     */
    public function getWeightConversionRate(): float;

    /**
     * Get configured weight unit.
     *
     * @return string
     */
    public function getWeightUnit(): string;

    /**
     * Get Bold API url.
     *
     * @return string
     */
    public function getApiUrl(): string;

    /**
     * Get Bold Checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl(): string;

    /**
     * Retrieve Bold shop identifier.
     *
     * @return string|null
     */
    public function getShopIdentifier(): ?string;

    /**
     * Set shop identifier.
     *
     * @param string $shopIdentifier
     * @return void
     */
    public function setShopIdentifier(string $shopIdentifier): void;
}
