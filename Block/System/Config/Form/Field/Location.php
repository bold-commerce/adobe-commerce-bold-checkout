<?php
declare(strict_types=1);

namespace Bold\Checkout\Block\System\Config\Form\Field;

use Bold\Checkout\Model\ModuleInfo\InstalledModulesProvider;
use Magento\Framework\View\Element\Html\Select;
use Magento\Backend\Block\Template\Context;

/**
 * HTML select element block with location options.
 */
class Location extends Select
{
    /**
     * @var InstalledModulesProvider
     */
    protected $installedModulesProvider;
    
    /**
     * @param Context $context
     * @param InstalledModulesProvider $installedModuleProvider
     */
    public function __construct(
        Context                  $context,
        InstalledModulesProvider $installedModulesProvider
    )
    {
        parent::__construct($context);
        $this->installedModulesProvider = $installedModulesProvider;
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return Location
     */
    public function setInputName(string $value): Location
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
        $options = [
            ['label' => 'At the top of the page', 'value' => 'main_content_beginning'],
            ['label' => 'Above the customer info section', 'value' => 'customer_info'],
            ['label' => 'Below the shipping address section', 'value' => 'shipping'],
            ['label' => 'Below the billing address section', 'value' => 'billing_address_after'],
            ['label' => 'Below the shipping method section', 'value' => 'shipping_lines'],
            ['label' => 'Above the payment method section', 'value' => 'payment_method_above'],
            ['label' => 'Below the payment method section', 'value' => 'payment_gateway'],
            ['label' => 'At the bottom of the main page, below the Complete order button', 'value' => 'below_actions'],
            ['label' => 'At the top of the summary sidebar', 'value' => 'summary_above_header'],
            ['label' => 'On the thank you page, below the thank you message', 'value' => 'thank_you_message'],
            ['label' => 'On the thank you page, below the order confirmation message', 'value' => 'order_confirmation'],
            ['label' => 'On the thank you page, below the order details', 'value' => 'order_details'],
            ['label' => 'At the bottom of the page', 'value' => 'main_content_end'],
        ];
        if ($this->installedModulesProvider->isPayPalFlowInstalled()) {
            $options[] = ['label' => '(PayPal Checkout Flow only) On the additional information page', 'value' => 'paypal_additional_information'];
        }
        
        return $options;
    }
}
