<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Block\System\Config\Form\Field;
use Bold\Checkout\Model\ModuleInfo\LatestModuleVersionProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Module latest available version field.
 */
class LatestVersion extends Field
{
    protected $unsetScope = true;

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var LatestModuleVersionProvider
     */
    private $latestModuleVersionProvider;

    /**
     * @param Context $context
     * @param ModuleComposerVersionProvider $moduleVersionProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        LatestModuleVersionProvider $latestModuleVersionProvider,
        string  $moduleName = '',
        array   $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleName = $moduleName;
        $this->latestModuleVersionProvider = $latestModuleVersionProvider;
    }

    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $version = $this->latestModuleVersionProvider->getVersion($this->moduleName);
        $element->setText($version);

        return parent::_renderValue($element);
    }
}
