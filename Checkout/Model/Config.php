<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Bold checkout config service.
 */
class Config implements ConfigInterface
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
    public function isCheckoutEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_ENABLED);
//            && Mage::helper('core')->isModuleOutputEnabled('Bold_Checkout');
    }

    /**
     * @inheritDoc
     */
    public function getEnabledFor(): int
    {
        return (int)$this->scopeConfig->getValue(self::PATH_ENABLED_FOR);
    }

    /**
     * @inheritDoc
     */
    public function getIpWhitelist(): array
    {
        $rawData = $this->scopeConfig->getValue(self::PATH_IP_WHITELIST);

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getCustomerWhitelist(): array
    {
        $rawData = $this->scopeConfig->getValue(self::PATH_CUSTOMER_WHITELIST);

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getOrdersPercentage(): int
    {
        return (int)$this->scopeConfig->getValue(self::PATH_ORDERS_PERCENTAGE);
    }

    /**
     * @inheritDoc
     */
    public function isRealtimeEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_REALTIME_ENABLED);
    }

    /**
     * @inheritDoc
     */
    public function getSharedSecret(): ?string
    {
        $encryptedSecret = $this->scopeConfig->getValue(self::PATH_SECRET);

        return $this->encryptor->decrypt($encryptedSecret);
    }

    /**
     * @inheritDoc
     */
    public function getApiToken(): ?string
    {
        $encryptedToken = $this->scopeConfig->getValue(self::PATH_TOKEN);

        return $this->encryptor->decrypt($encryptedToken);
    }

    /**
     * @inheritDoc
     */
    public function getWeightConversionRate(): float
    {
        return (float)$this->scopeConfig->getValue(self::PATH_WEIGHT_CONVERSION_RATE) ?: 1000;
    }

    /**
     * @inheritDoc
     */
    public function getWeightUnit(): string
    {
        return $this->scopeConfig->getValue(self::PATH_WEIGHT_UNIT) ?: 'kg';
    }

    /**
     * @inheritDoc
     */
    public function getApiUrl(): string
    {
        return rtrim($this->scopeConfig->getValue(self::PATH_API_URL), '/');
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutUrl(): string
    {
        return rtrim($this->scopeConfig->getValue(self::PATH_CHECKOUT_URL), '/');
    }

    /**
     * @inheritDoc
     */
    public function getShopIdentifier(): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_SHOP_IDENTIFIER);
    }

    /**
     * @inheritDoc
     */
    public function setShopIdentifier(string $shopIdentifier): void
    {
        $this->configWriter->save(self::PATH_SHOP_IDENTIFIER, $shopIdentifier);
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }
}
