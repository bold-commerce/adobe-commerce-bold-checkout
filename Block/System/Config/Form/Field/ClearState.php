<?php

declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Exception\LocalizedException;

/**
 * Clear Bold Integration configuration control button.
 */
class ClearState extends Field
{
    protected $unsetScope = true;
    protected $_template = 'Bold_Checkout::system/config/form/field/clear.phtml';

    /**
     * @param Context $context
     * @param FormKey $formKey
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formKey = $formKey;
    }

    /**
     * @inheritDoc
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Get button html code.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(Button::class)
            ->setData(
                [
                    'id' => 'clear-state',
                    'label' => __('Clear State'),
                ]
            );

        return $button->toHtml();
    }

    /**
     * Get website id from the html request parameter.
     *
     * @return int
     * @throws LocalizedException
     */
    private function getWebsiteId(): int
    {
        return (int)$this->getRequest()->getParam('website');
    }

    /**
     * Get URL of controller, responsible for configuration cleaning.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getClearUrl(): string
    {
        $websiteId = $this->getWebsiteId();

        return $this->getUrl('bold_checkout/clear', ['website' => $websiteId]);
    }
}
