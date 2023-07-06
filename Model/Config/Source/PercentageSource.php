<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * "Percentage" system configuration source class.
 */
class PercentageSource implements OptionSourceInterface
{
    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach (range(10, 90, 10) as $value) {
            $options[] =
                [
                    'value' => $value,
                    'label' => $value . '%'
                ];
        }

        return $options;
    }
}
