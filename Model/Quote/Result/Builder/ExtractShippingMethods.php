<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\Result\Builder;

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
        if ($quote->isVirtual()) {
            return [];
        }
        $quote->getShippingAddress()->requestShippingRates();
        $shippingRates = $quote->getShippingAddress()->getGroupedAllShippingRates();
        $shippingMethodSet = [];
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                // Filtering out the same shipping method
                if (isset($shippingMethodSet[$rate->getCode()])) {
                    continue;
                }
                $shippingMethodSet[$rate->getCode()] = true;

                $shippingMethods[] = $this->shippingMethodConverter->modelToDataObject(
                    $rate,
                    $quote->getQuoteCurrencyCode()
                );
            }
        }

        return $shippingMethods;
    }
}
