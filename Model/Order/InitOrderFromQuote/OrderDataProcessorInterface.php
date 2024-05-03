<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order\InitOrderFromQuote;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Process bold order data service interface.
 */
interface OrderDataProcessorInterface
{
    /**
     * Process bold order data.
     *
     * @param array $data
     * @param CartInterface $quote
     * @return array
     */
    public function process(array $data, CartInterface $quote): array;
}
