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
 * Bold Integration note field.
 */
class Note extends Field
{
    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $text = __('Bold Integration settings can by set on the Website scope level.');
        $element->setText($text);

        return parent::_renderValue($element);
    }
}
