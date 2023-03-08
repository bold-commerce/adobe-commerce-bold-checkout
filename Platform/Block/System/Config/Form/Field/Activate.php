<?php

declare(strict_types=1);

namespace Bold\Platform\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Integration;

/**
 * Bold Integration activation button.
 */
class Activate extends Field
{
    private const INTEGRATION_NAME = 'BoldPlatformIntegration';

    /**
     * @var \Magento\Integration\Api\IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $url;

    private $integration = null;

    /**
     * @param \Magento\Integration\Api\IntegrationServiceInterface $integrationService
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @param \Magento\Framework\View\Helper\SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        UrlInterface                $url,
        Context                     $context,
        array                       $data = [],
        ?SecureHtmlRenderer         $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->integrationService = $integrationService;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        /** @var \Magento\Backend\Block\Widget\Button $buttonBlock */
        $buttonBlock = $this->getForm()->getLayout()->createBlock(Button::class);
        $data = [
            'id' => 'bold_integration_activate',
            'label' => $this->getLabel(),
            'onclick' => sprintf('setLocation(\'%s\')', $this->getActivationUrl()),
        ];

        return $buttonBlock->setData($data)->toHtml();
    }

    /**
     * Get button label.
     *
     * @return \Magento\Framework\Phrase
     */
    private function getLabel(): Phrase
    {
        return !$this->isActivated()
            ? __('Activate')
            : __('Reauthorize');
    }

    /**
     * Check if the Integration is already activated.
     *
     * @return bool
     */
    private function isActivated(): bool
    {
        return $this->getIntegration()->getStatus() === Integration::STATUS_ACTIVE;
    }

    /**
     * Get Bold Integration.
     *
     * @return \Magento\Integration\Model\Integration
     */
    private function getIntegration(): Integration
    {
        if (!$this->integration) {
            $this->integration = $this->integrationService->findByName(self::INTEGRATION_NAME);
        }

        return $this->integration;
    }

    /**
     * Get activation URl.
     *
     * @return string
     */
    private function getActivationUrl(): string
    {
        $secret = $this->url->getSecretKey('adminhtml', 'integration', 'tokensExchange');

        return $this->getUrl(
            '*/integration/tokensExchange',
            [
                'id' => $this->getIntegration()->getId(),
                'reauthorize' => !$this->isActivated(),
                '_escape_params' => false,
                UrlInterface::SECRET_KEY_PARAM_NAME => $secret
            ]
        );
    }
}
