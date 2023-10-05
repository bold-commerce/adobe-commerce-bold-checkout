<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote\QuoteAction;

use Magento\Framework\Locale\CurrencyInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Gets currency for the active cart.
 */
class Currency implements QuoteActionInterface
{
    private const SET_DISPLAY_CURRENCY = 'set_display_currency';
    private const SET_CURRENCY = 'set_currency';
    private const SET_GATEWAY_CURRENCY = 'set_gateway_currency';

    /**
     * @var CurrencyInterface
     */
    private $currency;

    /**
     * @param CurrencyInterface $currency
     */
    public function __construct(CurrencyInterface $currency)
    {
        $this->currency = $currency;
    }

    /**
     * @inheritDoc
     */
    public function getActionData(CartInterface $cart): array
    {
        $cartCurrency = $cart->getCurrency();
        $currency = $this->currency->getCurrency($cartCurrency->getQuoteCurrencyCode());
        $currencyFormat = $currency->toCurrency("1");
        $format = preg_replace("/\d.*\d|\d/", "{{amount}}", $currencyFormat);
        return [
            [
                'type' => self::SET_DISPLAY_CURRENCY,
                'data' => [
                    'currency' => $cartCurrency->getQuoteCurrencyCode(),
                    'rate' => $cart->getCurrency()->getBaseToQuoteRate(),
                    'format_string' => $format,
                ],
            ],
            // Removing for now. More work is needing to be done to get this to work
            // [
            //     'type' => self::SET_GATEWAY_CURRENCY,
            //     'data' => [
            //         'currency' => $cartCurrency->getQuoteCurrencyCode(),
            //     ],
            // ],
        ];
    }
}
