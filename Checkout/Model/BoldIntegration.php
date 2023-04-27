<?php

declare(strict_types=1);

namespace Bold\Checkout\Model;

use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Config\Consolidated\Converter;
use Magento\Integration\Model\Integration;
use Magento\Store\Api\StoreWebsiteRelationInterface;

/**
 * Bold Integration model.
 */
class BoldIntegration
{
    private const INTEGRATION_NAME_TEMPLATE = 'BoldPlatformIntegration{{websiteId}}';

    private const API_RESOURCES = [
        'Magento_Backend::admin',
        'Bold_Checkout::integration',
        'Magento_Customer::customer',
        'Magento_Catalog::catalog',
        'Magento_Catalog::catalog_inventory',
        'Magento_Catalog::products',
        'Magento_Catalog::categories',
    ];

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
     * Update Bold Integration (if required).
     *
     * @param array $changedPaths
     * @param int $websiteId
     * @return void
     * @throws \Magento\Framework\Exception\IntegrationException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function update(array $changedPaths, int $websiteId)
    {
        $integrationName = $this->getName($websiteId);
        $integration = $this->integrationService->findByName($integrationName);
        $integrationId = $integration->getId();
        if (!array_intersect($changedPaths, Config::INTEGRATION_PATHS) && $integrationId) {
            return;
        }
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
            Integration::STATUS => Integration::STATUS_RECREATED,
            Converter::API_RESOURCES => self::API_RESOURCES
        ];
        $integration->getId()
            ? $this->integrationService->update($integrationData)
            : $this->integrationService->create($integrationData);
    }

    public function getName(int $websiteId) {
        return str_replace('{{websiteId}}', (string)$websiteId, self::INTEGRATION_NAME_TEMPLATE);
    }

    public function getStatus(int $websiteId) {
        $integration = $this->integrationService->findByName($this->getName($websiteId));

        return $integration->getId() ? $integration->getStatus() : null;
    }
}
