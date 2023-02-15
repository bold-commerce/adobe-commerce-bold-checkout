<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Config\Source;

use Bold\Checkout\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

class EnabledForSource implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => Config::VALUE_ENABLED_FOR_ALL, 'label' => __('All')],
            ['value' => Config::VALUE_ENABLED_FOR_IP, 'label' => __('Specific IPs')],
            ['value' => Config::VALUE_ENABLED_FOR_CUSTOMER, 'label' => __('Specific Customers')],
            ['value' => Config::VALUE_ENABLED_FOR_PERCENTAGE, 'label' => __('Percentage Of Orders')],
        ];
    }
}
