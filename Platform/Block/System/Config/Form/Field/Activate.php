<?php
declare(strict_types=1);

namespace Bold\Platform\Block\System\Config\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Model\UrlInterface;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Phrase;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Model\Integration;

/**
 * Bold Integration activation button.
 */
class Activate extends Field
{
    private const INTEGRATION_NAME = 'BoldPlatformIntegration';

    /**
     * @var IntegrationServiceInterface
     */
    private $integrationService;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Integration|null
     */
    private $integration = null;

    /**
     * @param IntegrationServiceInterface $integrationService
     * @param UrlInterface $url
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        IntegrationServiceInterface $integrationService,
        UrlInterface $url,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->integrationService = $integrationService;
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
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
     * @return Phrase
     */
    private function getLabel(): Phrase
    {
        return !$this->isActivated() ? __('Activate') : __('Reauthorize');
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
     * @return Integration
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
                UrlInterface::SECRET_KEY_PARAM_NAME => $secret,
            ]
        );
    }
}
