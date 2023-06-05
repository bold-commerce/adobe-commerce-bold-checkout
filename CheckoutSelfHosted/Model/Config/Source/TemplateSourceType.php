<?php
declare(strict_types=1);

namespace Bold\CheckoutSelfHosted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * "Bold Checkout Template Type" system configuration source class.
 */
class TemplateSourceType implements OptionSourceInterface
{
    public const VALUE_TYPE_ONE_PAGE = 'one_page';
    public const VALUE_TYPE_THREE_PAGE = 'three_page';

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::VALUE_TYPE_THREE_PAGE, 'label' => __('Three Page (Default)')],
            ['value' => self::VALUE_TYPE_ONE_PAGE, 'label' => __('One Page')],
        ];
    }
}
