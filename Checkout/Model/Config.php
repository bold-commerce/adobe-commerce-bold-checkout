<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Bold checkout config service.
 */
class Config implements ConfigInterface
{
    public const PATH_TOKEN = 'checkout/bold_checkout_base/api_token';
    public const PATH_SECRET = 'checkout/bold_checkout_base/shared_secret';
    private const PATH_ENABLED = 'checkout/bold_checkout_base/enabled';
    private const PATH_ENABLED_FOR = 'checkout/bold_checkout_advanced/enabled_for';
    private const PATH_IP_WHITELIST = 'checkout/bold_checkout_advanced/ip_whitelist';
    private const PATH_CUSTOMER_WHITELIST = 'checkout/bold_checkout_advanced/customer_whitelist';
    private const PATH_ORDERS_PERCENTAGE = 'checkout/bold_checkout_advanced/orders_percentage';
    private const PATH_API_URL = 'checkout/bold_checkout_advanced/api_url';
    private const PATH_CHECKOUT_URL = 'checkout/bold_checkout_advanced/checkout_url';
    private const PATH_WEIGHT_CONVERSION_RATE = 'checkout/bold_checkout_advanced/weight_conversion_rate';
    private const PATH_WEIGHT_UNIT = 'checkout/bold_checkout_advanced/weight_unit';
    private const PATH_SHOP_IDENTIFIER = 'checkout/bold_checkout_base/shop_identifier';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param EncryptorInterface $encryptor
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor,
        TypeListInterface $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @inheritDoc
     */
    public function isCheckoutEnabled(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_ENABLED, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getEnabledFor(int $websiteId): int
    {
        return (int)$this->scopeConfig->getValue(self::PATH_ENABLED_FOR, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getIpWhitelist(int $websiteId): array
    {
        $rawData = $this->scopeConfig->getValue(self::PATH_IP_WHITELIST, ScopeInterface::SCOPE_WEBSITES, $websiteId);

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getCustomerWhitelist(int $websiteId): array
    {
        $rawData = $this->scopeConfig->getValue(
            self::PATH_CUSTOMER_WHITELIST,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getOrdersPercentage(int $websiteId): int
    {
        return (int)$this->scopeConfig->getValue(
            self::PATH_ORDERS_PERCENTAGE,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getSharedSecret(int $websiteId): ?string
    {
        $encryptedSecret = $this->scopeConfig->getValue(self::PATH_SECRET, ScopeInterface::SCOPE_WEBSITES, $websiteId);

        return $this->encryptor->decrypt($encryptedSecret);
    }

    /**
     * @inheritDoc
     */
    public function getApiToken(int $websiteId): ?string
    {
        $encryptedToken = $this->scopeConfig->getValue(self::PATH_TOKEN, ScopeInterface::SCOPE_WEBSITES, $websiteId);

        return $this->encryptor->decrypt($encryptedToken);
    }

    /**
     * @inheritDoc
     */
    public function getWeightConversionRate(int $websiteId): float
    {
        return (float)$this->scopeConfig->getValue(
            self::PATH_WEIGHT_CONVERSION_RATE,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        ) ?: 1000;
    }

    /**
     * @inheritDoc
     */
    public function getWeightUnit(int $websiteId): string
    {
        return $this->scopeConfig->getValue(self::PATH_WEIGHT_UNIT, ScopeInterface::SCOPE_WEBSITES, $websiteId) ?: 'kg';
    }

    /**
     * @inheritDoc
     */
    public function getApiUrl(int $websiteId): string
    {
        return rtrim($this->scopeConfig->getValue(self::PATH_API_URL, ScopeInterface::SCOPE_WEBSITES, $websiteId), '/');
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutUrl(int $websiteId): string
    {
        return rtrim(
            $this->scopeConfig->getValue(
                self::PATH_CHECKOUT_URL,
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId
            ),
            '/');
    }

    /**
     * @inheritDoc
     */
    public function getShopIdentifier(int $websiteId): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_SHOP_IDENTIFIER, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function setShopIdentifier(int $websiteId, string $shopIdentifier): void
    {
        $this->configWriter->save(
            self::PATH_SHOP_IDENTIFIER,
            $shopIdentifier,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }
}
