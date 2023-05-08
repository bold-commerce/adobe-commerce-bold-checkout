<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\SetQuoteAddresses;

use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;

/**
 * Quote shipping methods extractor.
 */
class ExtractShippingMethods
{
    /**
     * @var ShippingMethodConverter
     */
    private $shippingMethodConverter;

    /**
     * @param ShippingMethodConverter $shippingMethodConverter
     */
    public function __construct(ShippingMethodConverter $shippingMethodConverter)
    {
        $this->shippingMethodConverter = $shippingMethodConverter;
    }

    /**
     * Extract shipping methods from quote.
     *
     * @param CartInterface $quote
     * @return ShippingMethodInterface[]
     */
    public function extract(CartInterface $quote): array
    {
        $shippingMethods = [];
        $shippingRates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $shippingMethods[] = $this->shippingMethodConverter->modelToDataObject(
                    $rate,
                    $quote->getQuoteCurrencyCode()
                );
            }
        }
        return $shippingMethods;
    }
}
