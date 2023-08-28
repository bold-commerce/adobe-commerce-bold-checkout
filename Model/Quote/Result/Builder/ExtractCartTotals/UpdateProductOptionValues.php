<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder\ExtractCartTotals;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Cart\Totals\Item;

/**
 * Update product option names to full if needed.
 */
class UpdateProductOptionValues
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(
        Json $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Update product option names to full if needed.
     *
     * @param Item[] $items
     * @return void
     */
    public function updateValues(array $items): void
    {
        foreach ($items as $item) {
            $options = $this->serializer->unserialize($item->getOptions());
            foreach ($options as $index => $option) {
                $options[$index]['value'] = \html_entity_decode($option['value']);
                if (isset($option['full_view'])) {
                    $options[$index]['value'] = \html_entity_decode($option['full_view']);
                }
            }
            $item->setOptions($this->serializer->serialize($options));
        }
    }
}
