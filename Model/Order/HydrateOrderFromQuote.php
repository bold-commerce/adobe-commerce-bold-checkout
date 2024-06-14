<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\ResultInterface;
use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Api\Order\HydrateOrderFromQuoteInterface;
use Bold\Checkout\Model\Order\Address\Converter;
use Bold\Checkout\Model\Quote\GetCartLineItems;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Address\ToOrderAddress;

/**
 * Hydrate Bold order from Magento quote.
 */
class HydrateOrderFromQuote implements HydrateOrderFromQuoteInterface
{
    private const HYDRATE_ORDER_URL = 'checkout_sidekick/{{shopId}}/order/%s';
    private const EXPECTED_SEGMENTS = [
        'subtotal',
        'shipping',
        'discount',
        'tax',
        'grand_total',
    ];

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
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @param ClientInterface $client
     * @param GetCartLineItems $getCartLineItems
     * @param Converter $addressConverter
     * @param ToOrderAddress $quoteToOrderAddressConverter
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ClientInterface $client,
        GetCartLineItems $getCartLineItems,
        Converter $addressConverter,
        ToOrderAddress $quoteToOrderAddressConverter,
        ProductFactory $productFactory
    ) {
        $this->client = $client;
        $this->getCartLineItems = $getCartLineItems;
        $this->addressConverter = $addressConverter;
        $this->quoteToOrderAddressConverter = $quoteToOrderAddressConverter;
        $this->productFactory = $productFactory;
    }

    /**
     * @inheritDoc
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

        list($fees, $discounts) = $this->getFeesAndDiscounts($totals);
        $discountTotal = array_reduce($discounts, function($sum, $discountLine) {
            return $sum + $discountLine['value'];
        });

        $cartItems = $this->getCartLineItems->getItems($quote);
        $formattedCartItems = $this->formatCartItems($cartItems);

        $body = [
            'billing_address' => $this->addressConverter->convert($billingAddress),
            'cart_items' => $formattedCartItems,
            'taxes' => $this->getTaxLines($totals['tax']['full_info']),
            'discounts' => $discounts,
            'fees' => $fees,
            'shipping_line' => [
                'rate_name' => $shippingDescription ?? '',
                'cost' => $this->convertToCents($totals['shipping']['value'])
            ],
            'totals' => [
                'sub_total' => $this->convertToCents($totals['subtotal']['value']),
                'tax_total' => $this->convertToCents($totals['tax']['value']),
                'discount_total' => $discountTotal ?? 0,
                'shipping_total' => $this->convertToCents($totals['shipping']['value']),
                'order_total' => $this->convertToCents($totals['grand_total']['value'])
            ],
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
    private function convertToCents($dollars): int
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

    /**
     * Looks at total segments and makes unrecognized segments into fees and discounts
     *
     * @param array $totals
     * @return array
     */
    private function getFeesAndDiscounts(array $totals): array
    {
        $fees = [];
        $discounts = [];

        if (isset($totals['discount'])) {
            $discounts[] = [
                'line_text' => $totals['discount']['code'],
                'value' => abs($this->convertToCents($totals['discount']['value']))
            ];
        }

        foreach ($totals as $segment) {
            if (in_array($segment['code'], self::EXPECTED_SEGMENTS) || !$segment['value']) {
                continue;
            }

            $description = $totalSegment['title'] ?? ucfirst(str_replace('_', ' ', $segment['code']));

            if ($segment['value'] > 0) {
                $fees[] = [
                    'description' => $description,
                    'value' => $this->convertToCents($segment['value'])
                ];
            } else {
                $discounts[] = [
                    'line_text' => $description,
                    'value' => abs($this->convertToCents($segment['value']))
                ];
            }
        }

        return [$fees, $discounts];
    }

    /**
     * @param array $cartItems
     * @return array
     */
    private function formatCartItems(array $cartItems): array
    {
        foreach ($cartItems as &$item) {
            $cartItem = $this->productFactory->create()->load($item['id']);
            $item['sku'] = $cartItem->getSku();
            $item['vendor'] = '';
        }

        return $cartItems;
    }
}
