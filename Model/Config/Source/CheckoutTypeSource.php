<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Config\Source;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * "Bold Checkout Type" system configuration source class.
 */
class CheckoutTypeSource implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => ConfigInterface::VALUE_TYPE_STANDARD, 'label' => __('Bold-Hosted (Standard)')],
            ['value' => ConfigInterface::VALUE_TYPE_PARALLEL, 'label' => __('Dual')],
            ['value' => ConfigInterface::VALUE_TYPE_SELF, 'label' => __('Payment Booster')],
            ['value' => ConfigInterface::VALUE_TYPE_SELF_REACT, 'label' => __('Self-Hosted')],
        ];
    }
}
