<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

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
        $text = __('All settings can by found on the Website scope level.');
        $element->setText($text);

        return parent::_renderValue($element);
    }
}
