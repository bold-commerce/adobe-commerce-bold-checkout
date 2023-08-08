<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

/**
 * HTML select element block with input required options.
 */
class InputRequired extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return InputRequired
     */
    public function setInputName(string $value): InputRequired
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
            ['label' => 'No', 'value' => 0],
            ['label' => 'Yes', 'value' => 1],
        ];
    }
}
