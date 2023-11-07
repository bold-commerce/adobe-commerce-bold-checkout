<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Block\System\Config\Form\Field;
use Bold\Checkout\Model\BoldIntegration;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Model\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Integration\Model\Integration\Source\Status as SourceStatus;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Bold Integration status field.
 */
class Status extends Field
{
    protected $unsetScope = true;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param BoldIntegration $boldIntegration
     * @param Config $config
     * @param SourceStatus $statusData
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        BoldIntegration $boldIntegration,
        Config $config,
        SourceStatus $statusData,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->boldIntegration = $boldIntegration;
        $this->config = $config;
        $this->statusData = $statusData;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $websiteId = (int)$this->config->getWebsite() ?: (int)$this->storeManager->getWebsite(true)->getId();
        $integrationName = $this->boldIntegration->getName($websiteId);
        $integrationStatus = $this->boldIntegration->getStatus($websiteId);
        $statusText = __('Not Found');
        foreach ($this->statusData->toOptionArray() as $statusDatum) {
            if ($statusDatum['value'] === $integrationStatus) {
                $statusText = $statusDatum['label'];
                break;
            }
        }
        $element->setText('<strong>' . $statusText . '</strong>');
        $commentTemplate = $element->getComment();
        $comment = str_replace('{{integrationName}}', $integrationName, (string)$commentTemplate);
        $element->setComment($comment);

        return parent::_renderValue($element);
    }
}
