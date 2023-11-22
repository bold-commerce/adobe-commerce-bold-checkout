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
     * @var ModuleComposerVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @param Context $context
     * @param LatestModuleVersionProvider $latestModuleVersionProvider
     * @param ModuleComposerVersionProvider $moduleVersionProvider
     * @param string $moduleName
     * @param array $data
     */
    public function __construct(
        Context $context,
        LatestModuleVersionProvider $latestModuleVersionProvider,
        ModuleComposerVersionProvider $moduleVersionProvider,
        string  $moduleName = '',
        array   $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleName = $moduleName;
        $this->latestModuleVersionProvider = $latestModuleVersionProvider;
        $this->moduleVersionProvider = $moduleVersionProvider;
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

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $latestVersion = $this->latestModuleVersionProvider->getVersion($this->moduleName);
        $currentVersion = $this->moduleVersionProvider->getVersion($this->moduleName);
        if (version_compare($latestVersion, $currentVersion, '>')) {
            return parent::render($element);
        }

        return '';
    }
}
