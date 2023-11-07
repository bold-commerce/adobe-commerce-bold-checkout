<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field as FormField;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Render field html element in Stores Configuration.
 */
class Field extends FormField
{
    /**
     * @var bool
     */
    protected $unsetScope = false;

    /**
     * Unset scope element parameters.
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->unsetScope) {
            $element = clone $element;
            $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        }

        return parent::render($element);
    }
}
