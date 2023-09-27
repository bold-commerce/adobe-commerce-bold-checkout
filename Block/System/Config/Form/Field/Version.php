<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\ModuleVersionProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

/**
 * Bold Integration version field.
 */
class Version extends Field
{
    /**
     * @var ModuleVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @param Context $context
     * @param ModuleVersionProvider $moduleVersionProvider
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        Context $context,
        ModuleVersionProvider $moduleVersionProvider,
        array $data = [],
        ?SecureHtmlRenderer $secureRenderer = null
    ) {
        parent::__construct($context, $data, $secureRenderer);
        $this->moduleVersionProvider = $moduleVersionProvider;
    }

    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $version = $this->moduleVersionProvider->getVersion('Bold_Checkout');
        $element->setText($version);

        return parent::_renderValue($element);
    }
}
