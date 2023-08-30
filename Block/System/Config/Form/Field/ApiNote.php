<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Bold Integration api note field.
 */
class ApiNote extends Field
{
    /**
     * @inheritDoc
     */
    protected function _renderValue(AbstractElement $element)
    {
        $text = __('Please switch website scopes first to add an API Token.');
        $element->setText($text);

        return parent::_renderValue($element);
    }
}
