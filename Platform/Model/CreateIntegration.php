<?php

declare(strict_types=1);

namespace Bold\Platform\Model;

use Bold\Platform\Helper\Oauth;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Integration\Model\Integration as IntegrationModel;

/**
 * Create a new or update an existing Integration.
 */
class CreateIntegration
{
    private const ACL_RESOURCES = [
        'Magento_Catalog::categories',
        'Magento_Catalog::products',
        'Magento_Customer::customer',
    ];

    private const CALLBACK_URL_TEMPLATE =
        'https://m2-platform-connector.staging.boldapps.net/magento/oauth/callback/%s';
    private const IDENTITY_URL = 'https://m2-platform-connector.staging.boldapps.net/magento/oauth/identify';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var \Magento\Integration\Api\OauthServiceInterface
     */
    private $oauthService;

    /**
     * @var \Bold\Platform\Helper\Oauth
     */
    private $oauthHelper;

    /**
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Integration\Api\OauthServiceInterface $oauthService
     * @param \Bold\Platform\Helper\Oauth $oauthHelper
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface       $oauthService,
        Oauth                       $oauthHelper
    ) {
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->oauthHelper = $oauthHelper;
    }

    /**
     * Create a new or update an existing Integration.
     *
     * @param string $name
     * @param string $token
     * @param string $secret
     * @return void
     * @throws \Magento\Framework\Exception\IntegrationException
     */
    public function create(string $name, string $token, string $secret)
    {
        $this->oauthHelper->setToken($token);
        $this->oauthHelper->setTokenSecret($secret);
        $integration = $this->integrationService->findByName($name);
        $integrationId = $integration->getId();
        $integrationData = [
            'integration_id' => $integrationId,
            'name' => $name,
            'endpoint' => sprintf(self::CALLBACK_URL_TEMPLATE, $token),
            'identity_link_url' => self::IDENTITY_URL,
            'all_resources' => false,
            'resource' => self::ACL_RESOURCES,
        ];
        $integration = $integrationId
            ? $this->integrationService->update($integrationData)
            : $this->integrationService->create($integrationData);
        if ($this->oauthService->createAccessToken($integration->getConsumerId(), true)) {
            $integration->setStatus(IntegrationModel::STATUS_ACTIVE)->save();
        }
    }
}
