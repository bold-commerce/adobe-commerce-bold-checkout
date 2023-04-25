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
    private const PATH_TOKEN = 'checkout/bold_checkout_base/api_token';
    private const PATH_SECRET = 'checkout/bold_checkout_base/shared_secret';
    private const PATH_ENABLED = 'checkout/bold_checkout_base/enabled';
    private const PATH_ENABLED_FOR = 'checkout/bold_checkout_advanced/enabled_for';
    private const PATH_IP_WHITELIST = 'checkout/bold_checkout_advanced/ip_whitelist';
    private const PATH_CUSTOMER_WHITELIST = 'checkout/bold_checkout_advanced/customer_whitelist';
    private const PATH_ORDERS_PERCENTAGE = 'checkout/bold_checkout_advanced/orders_percentage';
    private const PATH_PLATFORM_CONNECTOR_URL = 'checkout/bold_checkout_advanced/platform_connector_url';
    private const PATH_LOG_ENABLED = 'checkout/bold_checkout_advanced/log_enabled';
    private const PATH_INTEGRATION_EMAIL = 'checkout/bold_checkout_base/integration_email';
    private const PATH_INTEGRATION_CALLBACK_URL = 'checkout/bold_checkout_base/integration_callback_url';
    private const PATH_INTEGRATION_IDENTITY_URL = 'checkout/bold_checkout_base/integration_identity_url';

    public const INTEGRATION_PATHS = [
        self::PATH_INTEGRATION_EMAIL,
        self::PATH_INTEGRATION_CALLBACK_URL,
        self::PATH_INTEGRATION_IDENTITY_URL,
    ];

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
    public function getPlatformConnectorUrl(int $websiteId): string
    {
        return rtrim(
            $this->scopeConfig->getValue(
                self::PATH_PLATFORM_CONNECTOR_URL,
                ScopeInterface::SCOPE_WEBSITES,
                $websiteId),
            '/'
        );
    }

    /**
     * @inheritDoc
     */
    public function getShopId(int $websiteId): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_SHOP_ID, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getLogIsEnabled(int $websiteId): bool
    {
        return $this->scopeConfig->isSetFlag(self::PATH_LOG_ENABLED, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function setShopId(int $websiteId, string $shopId): void
    {
        $this->configWriter->save(
            self::PATH_SHOP_ID,
            $shopId,
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }

    /**
     * @inheritDoc
     */
    public function setSharedSecret(int $websiteId, string $sharedSecret): void
    {
        $this->configWriter->save(
            self::PATH_SECRET,
            $this->encryptor->encrypt($sharedSecret),
            ScopeInterface::SCOPE_WEBSITES,
            $websiteId
        );
        $this->cacheTypeList->cleanType('config');
        $this->scopeConfig->clean();
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationEmail(int $websiteId): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_INTEGRATION_EMAIL, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationCallbackUrl(int $websiteId): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_INTEGRATION_CALLBACK_URL, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationIdentityLinkUrl(int $websiteId): ?string
    {
        return $this->scopeConfig->getValue(self::PATH_INTEGRATION_IDENTITY_URL, ScopeInterface::SCOPE_WEBSITES, $websiteId);
    }
}
