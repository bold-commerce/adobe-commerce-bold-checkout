<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Block\System\Config\Form\Field;
use Bold\Checkout\Model\ModuleInfo\InstalledModulesProvider;
use Bold\Checkout\Model\ModuleInfo\LatestModuleVersionProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerNameProvider;
use Bold\Checkout\Model\ModuleInfo\ModuleComposerVersionProvider;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Bold Integration module version field.
 */
class Versions extends Field
{
    protected $unsetScope = true;

    /**
     * @var ModuleComposerVersionProvider
     */
    private $moduleVersionProvider;

    /**
     * @var InstalledModulesProvider
     */
    private $installedModulesProvider;

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
        Context                       $context,
        ModuleComposerVersionProvider $moduleVersionProvider,
        LatestModuleVersionProvider   $latestModuleVersionProvider,
        InstalledModulesProvider      $installedModulesProvider,
        array                         $data = []
    )
    {
        parent::__construct($context, $data);
        $this->moduleVersionProvider = $moduleVersionProvider;
        $this->installedModulesProvider = $installedModulesProvider;
        $this->latestModuleVersionProvider = $latestModuleVersionProvider;
    }

    /**
     * @inheritDoc
     */
    public function render(AbstractElement $element)
    {
        $result = '';
        foreach ($this->installedModulesProvider->getModuleList() as $module) {
            $element->setLabel(__('%1 version', $module));
            $element->setHtmlId('checkout_bold_module_version_' . strtolower($module));
            $currentVersion = $this->moduleVersionProvider->getVersion($module);
            $latestVersion = $this->latestModuleVersionProvider->getVersion($module);
            $text = $currentVersion;
            if (version_compare($latestVersion, $currentVersion, '>')) {
                $text .= ' (' .  __('update available') . ')';
            }
            $element->setText($text);
            $result .= parent::render($element);
        }

        return $result;
    }
}
