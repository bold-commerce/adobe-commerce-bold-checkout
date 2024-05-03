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
    public const VALUE_TYPE_STANDARD = 0;
    public const VALUE_TYPE_PARALLEL = 1;
    public const VALUE_TYPE_SELF = 2;
    public const VALUE_TYPE_SELF_REACT = 3;
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
     * Get is bold checkout log is enabled.
     *
     * @param int $websiteId
     * @return bool
     */
    public function getLogIsEnabled(int $websiteId): bool;

    /**
     * Set shop identifier.
     *
     * @param int $websiteId
     * @param string|null $shopId
     * @return void
     */
    public function setShopId(int $websiteId, ?string $shopId): void;

    /**
     * Get integration Email.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getIntegrationEmail(int $websiteId): ?string;

    /**
     * Get integration Callback URL.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getIntegrationCallbackUrl(int $websiteId): ?string;

    /**
     * Get integration API URL.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getApiUrl(int $websiteId): ?string;

    /**
     * Get integration Checkout URL.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getCheckoutUrl(int $websiteId): ?string;

    /**
     * Get integration Identity link URL.
     *
     * @param int $websiteId
     * @return string|null
     */
    public function getIntegrationIdentityLinkUrl(int $websiteId): ?string;

    /**
     * Check if Bold Checkout type is standard.
     *
     * @param int $websiteId
     * @return bool
     */
    public function isCheckoutTypeStandard(int $websiteId): bool;

    /**
     * Check if Bold Checkout type is parallel.
     *
     * @param int $websiteId
     * @return bool
     */
    public function isCheckoutTypeParallel(int $websiteId): bool;

    /**
     * Check if Bold Checkout type is self-hosted (Magento storefront).
     *
     * @param int $websiteId
     * @return bool
     */
    public function isCheckoutTypeSelfHosted(int $websiteId): bool;

    /**
     * Check if Bold Checkout type is self-hosted (React application).
     *
     * @param int $websiteId
     * @return bool
     */
    public function isCheckoutTypeSelfHostedReact(int $websiteId): bool;

    /**
     * Get Bold Payment storefront title.
     *
     * @param int $websiteId
     * @return string
     */
    public function getPaymentTitle(int $websiteId): string;

    /**
     * Get Bold Checkout button title.
     *
     * @param int $websiteId
     * @return string
     */
    public function getParallelCheckoutButtonTitle(int $websiteId): string;

    /**
     * Get Bold Checkout (LiFE) elements.
     *
     * @param int $websiteId
     * @return array
     */
    public function getLifeElements(int $websiteId): array;

    /**
     * Get payment iframe additional css.
     *
     * @param int $websiteId
     * @return string
     */
    public function getPaymentCss(int $websiteId): string;

    /**
     * Should validate coupon codes.
     *
     * @param int $websiteId
     * @return bool
     */
    public function getValidateCouponCodes(int $websiteId): bool;

    /**
     * Check if module update check is available.
     *
     * @return bool
     */
    public function isUpdatesCheckEnabled(): bool;
}
