<?php

namespace Bold\Checkout\Model\Order\InitOrderFromQuote;

use Magento\Quote\Api\Data\CartInterface;

interface OrderDataProcessorInterface
{
    /**
     * Process order data.
     *
     * @param array $data
     * @param CartInterface $quote
     * @return array
     */
    public function process(array $data, CartInterface $quote): array;
}
