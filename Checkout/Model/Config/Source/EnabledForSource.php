<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Config\Source;

use Bold\Checkout\Model\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

class EnabledForSource implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => ConfigInterface::VALUE_ENABLED_FOR_ALL, 'label' => __('All')],
            ['value' => ConfigInterface::VALUE_ENABLED_FOR_IP, 'label' => __('Specific IPs')],
            ['value' => ConfigInterface::VALUE_ENABLED_FOR_CUSTOMER, 'label' => __('Specific Customers')],
            ['value' => ConfigInterface::VALUE_ENABLED_FOR_PERCENTAGE, 'label' => __('Percentage Of Orders')],
        ];
    }
}
