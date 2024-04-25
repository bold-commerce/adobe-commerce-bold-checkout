<?php
declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\ConfigManagementInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

/**
 * Bold checkout config service.
 */
class Config implements ConfigInterface
{
    private const PATH_TOKEN = 'checkout/bold_checkout_base/api_token';
    private const PATH_SECRET = 'checkout/bold_checkout_base/shared_secret';
    private const PATH_ENABLED = 'checkout/bold_checkout_base/enabled';
    private const PATH_TYPE = 'checkout/bold_checkout_base/type';
    private const PATH_PAYMENT_TITLE = 'checkout/bold_checkout_base/payment_title';
    private const PATH_PARALLEL_CHECKOUT_BUTTON_TITLE = 'checkout/bold_checkout_base/parallel_checkout_button_title';
    private const PATH_ENABLED_FOR = 'checkout/bold_checkout_advanced/enabled_for';
    private const PATH_IP_WHITELIST = 'checkout/bold_checkout_advanced/ip_whitelist';
    private const PATH_CUSTOMER_WHITELIST = 'checkout/bold_checkout_advanced/customer_whitelist';
    private const PATH_ORDERS_PERCENTAGE = 'checkout/bold_checkout_advanced/orders_percentage';
    private const PATH_PLATFORM_CONNECTOR_URL = 'checkout/bold_checkout_advanced/platform_connector_url';
    private const PATH_LOG_ENABLED = 'checkout/bold_checkout_advanced/log_enabled';
    private const PATH_INTEGRATION_EMAIL = 'checkout/bold_checkout_base/integration_email';
    private const PATH_INTEGRATION_CALLBACK_URL = 'checkout/bold_checkout_base/integration_callback_url';
    private const PATH_INTEGRATION_API_URL = 'checkout/bold_checkout_advanced/api_url';
    private const PATH_INTEGRATION_CHECKOUT_URL = 'checkout/bold_checkout_advanced/checkout_url';
    private const PATH_INTEGRATION_IDENTITY_URL = 'checkout/bold_checkout_base/integration_identity_url';
    private const PATH_LIFE_ELEMENTS = 'checkout/bold_checkout_life_elements/life_elements';
    private const PATH_VALIDATE_COUPON_CODES = 'checkout/bold_checkout_advanced/validate_coupon_codes';
    private const PATH_UPDATE_CHECK = 'checkout/bold_checkout_advanced/updates_check';

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
     * @var ConfigManagementInterface
     */
    private $configManagement;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ConfigManagementInterface $configManagement
     * @param WriterInterface $configWriter
     * @param EncryptorInterface $encryptor
     * @param TypeListInterface $cacheTypeList
     * @param Json $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConfigManagementInterface $configManagement,
        WriterInterface $configWriter,
        EncryptorInterface $encryptor,
        TypeListInterface $cacheTypeList,
        Json $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configManagement = $configManagement;
        $this->configWriter = $configWriter;
        $this->encryptor = $encryptor;
        $this->cacheTypeList = $cacheTypeList;
        $this->serializer = $serializer;
    }

    /**
     * @inheritDoc
     */
    public function isCheckoutEnabled(int $websiteId): bool
    {
        return $this->configManagement->isSetFlag(
            self::PATH_ENABLED,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getEnabledFor(int $websiteId): int
    {
        return (int)$this->configManagement->getValue(
            self::PATH_ENABLED_FOR,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getIpWhitelist(int $websiteId): array
    {
        $rawData = $this->configManagement->getValue(
            self::PATH_IP_WHITELIST,
            $websiteId
        );

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getCustomerWhitelist(int $websiteId): array
    {
        $rawData = $this->configManagement->getValue(
            self::PATH_CUSTOMER_WHITELIST,
            $websiteId
        );

        return $rawData ? array_filter(array_map('trim', explode(',', $rawData))) : [];
    }

    /**
     * @inheritDoc
     */
    public function getOrdersPercentage(int $websiteId): int
    {
        return (int)$this->configManagement->getValue(
            self::PATH_ORDERS_PERCENTAGE,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getSharedSecret(int $websiteId): ?string
    {
        $encryptedSecret = $this->configManagement->getValue(
            self::PATH_SECRET,
            $websiteId
        );

        return $this->encryptor->decrypt($encryptedSecret);
    }

    /**
     * @inheritDoc
     */
    public function getApiToken(int $websiteId): ?string
    {
        $encryptedToken = $this->configManagement->getValue(
            self::PATH_TOKEN,
            $websiteId
        );

        return $this->encryptor->decrypt($encryptedToken);
    }

    /**
     * @inheritDoc
     */
    public function getPlatformConnectorUrl(int $websiteId): string
    {
        return rtrim(
            $this->configManagement->getValue(
                self::PATH_PLATFORM_CONNECTOR_URL,
                $websiteId
            ),
            '/'
        );
    }

    /**
     * @inheritDoc
     */
    public function getShopId(int $websiteId): ?string
    {
        return $this->configManagement->getValue(
            self::PATH_SHOP_ID,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getLogIsEnabled(int $websiteId): bool
    {
        return $this->configManagement->isSetFlag(
            self::PATH_LOG_ENABLED,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function setShopId(int $websiteId, ?string $shopId): void
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
        return $this->configManagement->getValue(
            self::PATH_INTEGRATION_EMAIL,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationCallbackUrl(int $websiteId): ?string
    {
        return $this->configManagement->getValue(
            self::PATH_INTEGRATION_CALLBACK_URL,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getApiUrl(int $websiteId): ?string
    {
        return $this->configManagement->getValue(
            self::PATH_INTEGRATION_API_URL,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getCheckoutUrl(int $websiteId): ?string
    {
        return $this->configManagement->getValue(
            self::PATH_INTEGRATION_CHECKOUT_URL,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationIdentityLinkUrl(int $websiteId): ?string
    {
        return $this->configManagement->getValue(
            self::PATH_INTEGRATION_IDENTITY_URL,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function isCheckoutTypeStandard(int $websiteId): bool
    {
        return (int)$this->configManagement->getValue(
                self::PATH_TYPE,
                $websiteId
            ) === ConfigInterface::VALUE_TYPE_STANDARD;
    }

    /**
     * @inheritDoc
     */
    public function isCheckoutTypeParallel(int $websiteId): bool
    {
        return (int)$this->configManagement->getValue(
                self::PATH_TYPE,
                $websiteId
            ) === ConfigInterface::VALUE_TYPE_PARALLEL;
    }

    /**
     * @inheritDoc
     */
    public function getPaymentTitle(int $websiteId): string
    {
        return (string)$this->configManagement->getValue(
            self::PATH_PAYMENT_TITLE,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getParallelCheckoutButtonTitle(int $websiteId): string
    {
        return (string)$this->configManagement->getValue(
            self::PATH_PARALLEL_CHECKOUT_BUTTON_TITLE,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function getLifeElements(int $websiteId): array
    {
        $lifeElements = $this->configManagement->getValue(
            self::PATH_LIFE_ELEMENTS,
            $websiteId
        );

        if (!$lifeElements) {
            return [];
        }

        $lifeElements = $this->serializer->unserialize($lifeElements);
        return is_array($lifeElements) ? $lifeElements : [];
    }

    /**
     * @inheirtDoc
     */
    public function getValidateCouponCodes(int $websiteId): bool
    {
        return $this->configManagement->isSetFlag(
            self::PATH_VALIDATE_COUPON_CODES,
            $websiteId
        );
    }

    /**
     * @inheritDoc
     */
    public function isUpdatesCheckEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::PATH_UPDATE_CHECK
        );
    }
}
