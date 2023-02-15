<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    private const PATH_ENABLED = 'checkout/bold_checkout_base/enabled';
    private const PATH_ENABLED_FOR = 'checkout/bold_checkout_advanced/enabled_for';
    private const PATH_IP_WHITELIST = 'checkout/bold_checkout_advanced/ip_whitelist';
    private const PATH_CUSTOMER_WHITELIST = 'checkout/bold_checkout_advanced/customer_whitelist';
    private const PATH_ORDERS_PERCENTAGE = 'checkout/bold_checkout_advanced/orders_percentage';
    private const PATH_REALTIME_ENABLED = 'checkout/bold_checkout_advanced/realtime_enabled';
    private const PATH_SECRET = 'checkout/bold_checkout_base/shared_secret';
    private const PATH_TOKEN = 'checkout/bold_checkout_base/api_token';
    private const PATH_API_URL = 'checkout/bold_checkout_advanced/api_url';
    private const PATH_CHECKOUT_URL = 'checkout/bold_checkout_advanced/checkout_url';
    private const PATH_WEIGHT_CONVERSION_RATE = 'checkout/bold_checkout_advanced/weight_conversion_rate';
    private const PATH_WEIGHT_UNIT = 'checkout/bold_checkout_advanced/weight_unit';
    private const PATH_SHOP_IDENTIFIER = 'checkout/bold_checkout_base/shop_identifier';

    /**
     * Values for self::PATH_ENABLED_FOR field.
     */
    public const VALUE_ENABLED_FOR_ALL = 0;
    public const VALUE_ENABLED_FOR_IP = 1;
    public const VALUE_ENABLED_FOR_CUSTOMER = 2;
    public const VALUE_ENABLED_FOR_PERCENTAGE = 3;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private EncryptorInterface $encryptor;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private WriterInterface $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private TypeListInterface $cacheTypeList;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface      $configWriter,
        EncryptorInterface   $encryptor,
        TypeListInterface    $cacheTypeList
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * Check if bold functionality enabled.
     *
     * @return bool
     */
    public function isCheckoutEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::PATH_ENABLED);
//            && Mage::helper('core')->isModuleOutputEnabled('Bold_Checkout');
    }

    /**
     * Show if Bold functionality is enabled for specific customers.
     *
     * @return int
     */
    public function getEnabledFor(): int
    {
        return (int)$this->scopeConfig->getValue(self::PATH_ENABLED_FOR);
    }

    /**
     * Get IP whitelist.
     *
     * @return string[]
     */
    public function getIpWhitelist(): array
    {
        $rawData = $this->scopeConfig->getValue(self::PATH_IP_WHITELIST);

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * Get Customer email whitelist.
     *
     * @return string[]
     */
    public function getCustomerWhitelist(): array
    {
        $rawData = $this->scopeConfig->getValue(self::PATH_CUSTOMER_WHITELIST);

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * Get Orders percentage.
     *
     * @return int
     */
    public function getOrdersPercentage(): int
    {
        return (int)$this->scopeConfig->getValue(self::PATH_ORDERS_PERCENTAGE);
    }

    /**
     * Check if real-time synchronization is enabled.
     *
     * @return bool
     */
    public function isRealtimeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_REALTIME_ENABLED);
    }

    /**
     * Get shared secret key (decrypted).
     *
     * @return string|null
     */
    public function getSharedSecret(): ?string
    {
        $encryptedSecret = $this->scopeConfig->getValue(self::PATH_SECRET);

        return $this->encryptor->decrypt($encryptedSecret);
    }

    /**
     * Get api token (decrypted).
     *
     * @return string|null
     */
    public function getApiToken(): ?string
    {
        $encryptedToken = $this->scopeConfig->getValue(self::PATH_TOKEN);

        return $this->encryptor->decrypt($encryptedToken);
    }

    /**
     * Get configured weight unit to grams conversion rate.
     *
     * @return int
     */
    public function getWeightConversionRate(): float
    {
        return (float)$this->scopeConfig->getValue(self::PATH_WEIGHT_CONVERSION_RATE) ?: 1000;
    }

    /**
     * Get configured weight unit.
     *
     * @return string
     */
    public function getWeightUnit(): string
    {
        return $this->scopeConfig->getValue(self::PATH_WEIGHT_UNIT) ?: 'kg';
    }

    /**
     * Get Bold API url.
     *
     * @return string
     */
    public function getApiUrl(): string
    {
        return rtrim($this->scopeConfig->getValue(self::PATH_API_URL), '/');
    }

    /**
     * Get Bold Checkout url.
     *
     * @return string
     */
    public function getCheckoutUrl(): string
    {
        return rtrim($this->scopeConfig->getValue(self::PATH_CHECKOUT_URL), '/');
    }

    /**
     * Retrieve Bold shop identifier.
     *
     * @return string|null
     */
    public function getShopIdentifier(): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_SHOP_IDENTIFIER);
    }

    /**
     * Retrieve Bold shop identifier.
     *
     * @param string $shopIdentifier
     * @return void
     */
    public function saveShopIdentifier($shopIdentifier): void
    {
        $this->configWriter->save(self::PATH_SHOP_IDENTIFIER, $shopIdentifier);
        $this->cacheTypeList->cleanType('config');
    }
}
