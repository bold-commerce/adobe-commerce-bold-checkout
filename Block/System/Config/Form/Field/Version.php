<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\ModuleVersionProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

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
     */
    public function __construct(
        Context $context,
        ModuleVersionProvider $moduleVersionProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
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
