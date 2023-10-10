<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

/**
 * HTML select element block with input type options.
 */
class InputType extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return InputType
     */
    public function setInputName(string $value): InputType
    {
        return $this->setName($value);
    }

    /**
     * @inheirtDoc
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }

        return parent::_toHtml();
    }

    /**
     * Retrieve source options.
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        return [
            ['label' => 'Text', 'value' => 'text'],
            ['label' => 'Textarea', 'value' => 'textarea'],
            ['label' => 'Checkbox', 'value' => 'checkbox'],
            ['label' => 'HTML', 'value' => 'html'],
            ['label' => 'Dropdown', 'value' => 'dropdown'],
            ['label' => 'Datepicker', 'value' => 'datepicker'],
        ];
    }
}
