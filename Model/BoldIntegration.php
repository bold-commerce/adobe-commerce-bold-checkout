<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Bold\Checkout\Api\IntegrationInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Config\Consolidated\Converter;
use Magento\Integration\Model\Integration;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Bold Integration model.
 */
class BoldIntegration implements IntegrationInterface
{
    private const API_RESOURCES = [
        'Bold_Checkout::integration',
        'Bold_Checkout::secret_create',
        'Magento_Backend::store',
        'Magento_Catalog::products',
        'Magento_Catalog::sets',
        'Magento_InventoryApi::stock',
        'Magento_Catalog::categories',
        'Magento_Customer::customer',
        'Magento_Customer::manage',
        'Magento_Customer::delete',
        'Magento_Sales::create',
        'Magento_Catalog::catalog_inventory',
        'Magento_InventorySalesApi::stock',
        'Magento_Sales::actions_view',
        'Magento_Tax::manage_tax',
        'Magento_Cart::manage',
    ];

    private const INTEGRATION_NAME_TEMPLATE = 'BoldPlatformIntegration{{websiteId}}';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var SenderResolverInterface
     */
    private $senderResolver;

    /**
     * @var StoreWebsiteRelationInterface
     */
    private $storeWebsiteRelation;

    /**
     * @param ConfigInterface $config
     * @param IntegrationServiceInterface $integrationService
     * @param SenderResolverInterface $senderResolver
     * @param StoreWebsiteRelationInterface $storeWebsiteRelation
     */
    public function __construct(
        ConfigInterface $config,
        IntegrationServiceInterface $integrationService,
        SenderResolverInterface $senderResolver,
        StoreWebsiteRelationInterface $storeWebsiteRelation
    ) {
        $this->config = $config;
        $this->integrationService = $integrationService;
        $this->senderResolver = $senderResolver;
        $this->storeWebsiteRelation = $storeWebsiteRelation;
    }

    /**
     * @inheritDoc
     */
    public function update(int $websiteId): void
    {
        $integrationName = $this->getName($websiteId);
        $integration = $this->integrationService->findByName($integrationName);
        $integrationId = $integration->getId();
        $shopId = $this->config->getShopId($websiteId);
        $senderIdentity = $this->config->getIntegrationEmail($websiteId);
        $storeIds = $this->storeWebsiteRelation->getStoreByWebsiteId($websiteId);
        $sender = $this->senderResolver->resolve($senderIdentity, reset($storeIds));
        $email = $sender['email'] ?? '';
        $callbackUrl = $this->config->getIntegrationCallbackUrl($websiteId);
        $identityUrl = $this->config->getIntegrationIdentityLinkUrl($websiteId);
        $endpointUrl = str_replace('{{shopId}}', $shopId, $callbackUrl);
        $integrationData = [
            Integration::ID => $integrationId,
            Integration::NAME => $integrationName,
            Integration::EMAIL => $email,
            Integration::ENDPOINT => $endpointUrl,
            Integration::IDENTITY_LINK_URL => $identityUrl,
            Integration::SETUP_TYPE => Integration::TYPE_MANUAL,
            Integration::STATUS => Integration::STATUS_INACTIVE,
            Converter::API_RESOURCES => self::API_RESOURCES,
        ];
        $integration->getId()
            ? $this->integrationService->update($integrationData)
            : $this->integrationService->create($integrationData);
    }

    /**
     * Get integration name.
     *
     * @param int $websiteId
     * @return string
     */
    public function getName(int $websiteId): string
    {
        return str_replace('{{websiteId}}', (string)$websiteId, self::INTEGRATION_NAME_TEMPLATE);
    }

    /**
     * Get integration status.
     *
     * @param int $websiteId
     * @return int|null
     */
    public function getStatus(int $websiteId): ?int
    {
        $integration = $this->integrationService->findByName($this->getName($websiteId));

        return $integration->getId() ? $integration->getStatus() : null;
    }
}
