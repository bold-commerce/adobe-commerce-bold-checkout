<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\BoldIntegration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use Magento\Integration\Model\Integration\Source\Status as SourceStatus;

/**
 * Bold Integration status field.
 */
class Status extends Field
{
    /**
     * @var BoldIntegration
     */
    private $boldIntegration;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SourceStatus
     */
    private $statusData;

    /**
     * @param Context $context
     * @param BoldIntegration $boldIntegration
     * @param Config $config
     * @param SourceStatus $statusData
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        BoldIntegration $boldIntegration,
        Config $config,
        SourceStatus $statusData,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
        $this->boldIntegration = $boldIntegration;
        $this->config = $config;
        $this->statusData = $statusData;
    }

    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $websiteId = (int)$this->config->getWebsite();
        $integrationName = $this->boldIntegration->getName($websiteId);
        $integrationStatus = $this->boldIntegration->getStatus($websiteId);
        $statusText = __('Not Found');
        foreach ($this->statusData->toOptionArray() as $statusDatum) {
            if ($statusDatum['value'] === $integrationStatus) {
                $statusText =  $statusDatum['label'];
                break;
            }
        }
        $element->setText('<strong>' . $statusText. '</strong>');
        $commentTemplate = $element->getComment();
        $comment = str_replace('{{integrationName}}', $integrationName, (string)$commentTemplate);
        $element->setComment($comment);

        return parent::_renderValue($element);
    }
}
