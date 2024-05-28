<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Order\HydrateOrderFromQuoteInterface;
use Bold\Checkout\Model\Order\Address\Converter;
use Bold\Checkout\Model\Quote\GetCartLineItems;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

/**
 * Hydrate Bold order from Magento quote.
 */
class HydrateOrderFromQuote implements HydrateOrderFromQuoteInterface
{
    private const HYDRATE_ORDER_URL = 'checkout_sidekick/{{shopId}}/order/%s';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var GetCartLineItems
     */
    private $getCartLineItems;

    /**
     * @var Converter
     */
    private $addressConverter;

    /**
     * @var ToOrderAddress
     */
    private $quoteToOrderAddressConverter;


    /**
     * @param ClientInterface $client
     * @param GetCartLineItems $getCartLineItems
     * @param Converter $addressConverter
     * @param ToOrderAddress $quoteToOrderAddressConverter
     */
    public function __construct(
        ClientInterface $client,
        GetCartLineItems $getCartLineItems,
        Converter $addressConverter,
        ToOrderAddress $quoteToOrderAddressConverter,
    ) {
        $this->client = $client;
        $this->getCartLineItems = $getCartLineItems;
        $this->addressConverter = $addressConverter;
        $this->quoteToOrderAddressConverter = $quoteToOrderAddressConverter;
    }

    /**
     * @param CartInterface $quote
     * @param string $publicOrderId
     * @return ResultInterface
     */
    public function hydrate(CartInterface $quote, string $publicOrderId): ResultInterface
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $billingAddress = $this->quoteToOrderAddressConverter->convert($quote->getBillingAddress());

        if ($quote->getIsVirtual()) {
            $totals = $quote->getBillingAddress()->getTotals();
        } else {
            $totals = $quote->getShippingAddress()->getTotals();
            $shippingDescription = $quote->getShippingAddress()->getShippingDescription();
        }

        $discountLine = [];
        $discountTotal = 0;

        if (isset($totals['discount'])) {
            $discountLine[] = [
                'line_text' => $totals['discount']['code'],
                'value' => abs($this->convertToCents($totals['discount']['value']))
            ];
            $discountTotal = abs($this->convertToCents($totals['discount']['value']));
        }

        $body = [
            'billing_address' => $this->addressConverter->convert($billingAddress),
            'cart_items' => $this->getCartLineItems->getItems($quote),
            'taxes' => $this->getTaxLines($totals['tax']['full_info']),
            'discounts' => $discountLine,
            'shipping_line' => [
                'rate_name' => $shippingDescription ?? '',
                'cost' => $this->convertToCents($totals['shipping']['value'])
            ],
            'totals' => [
                'sub_total' => $this->convertToCents($totals['subtotal']['value']),
                'tax_total' => $this->convertToCents($totals['tax']['value']),
                'discount_total' => $discountTotal,
                'shipping_total' => $this->convertToCents($totals['shipping']['value']),
                'order_total' => $this->convertToCents($totals['grand_total']['value'])
            ],
            'fees' => [],
        ];

        if ($quote->getCustomer()->getId()) {
            $body['customer'] = [
                'platform_id' => (string)$quote->getCustomerId(),
                'first_name' => $quote->getCustomerFirstname(),
                'last_name' => $quote->getCustomerLastname(),
                'email_address' => $quote->getCustomerEmail(),
            ];
        } else {
            $body['customer'] = [
                'platform_id' => null,
                'first_name' => $billingAddress->getFirstname(),
                'last_name' => $billingAddress->getLastname(),
                'email_address' => $billingAddress->getEmail(),
            ];
        }

        $url = sprintf(self::HYDRATE_ORDER_URL, $publicOrderId);
        return $this->client->put($websiteId, $url, $body);
    }

    /**
     * Converts a dollar amount to cents
     *
     * @param float|string $dollars
     * @return integer
     */
    private function convertToCents(float|string $dollars): int
    {
        return (int)round(floatval($dollars) * 100);
    }

    /**
     * Get formatted tax lines
     *
     * @param array $taxes
     * @return array
     */
    private function getTaxLines(array $taxes): array
    {
        $taxLines = [];

        foreach ($taxes as $tax){
            $taxLines[] = [
                'name' => $tax['id'],
                'value' => $this->convertToCents($tax['base_amount'])
            ];
        }

        return $taxLines;
    }
}
