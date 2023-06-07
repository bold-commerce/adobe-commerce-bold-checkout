<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Model\QuoteManagement;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;

/**
 * Send taxes and discount to Bold.
 */
class SendTaxesAndDiscountPlugin
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ClientInterface $client
     * @param Session $checkoutSession
     */
    public function __construct(ClientInterface $client, Session $checkoutSession)
    {
        $this->client = $client;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Send taxes and discount to Bold.
     *
     * @param QuoteManagement $subject
     * @param CartInterface $quote
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSubmit(QuoteManagement $subject, CartInterface $quote)
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return;
        }
        if ($quote->getPayment()->getMethod() !== 'bold') {
            return;
        }
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $taxes = $this->client->post($websiteId, 'taxes', []);
        if ($taxes->getErrors()) {
            throw new LocalizedException(__('Unable to set taxes.'));
        }
        if ($quote->getBaseSubtotal() - $quote->getBaseSubtotalWithDiscount()) {
            $discount = $this->client->post($websiteId, 'discounts', ['code' => 'Discount']);
            if ($discount->getErrors()) {
                throw new LocalizedException(__('Unable to set discounts.'));
            }
        }
    }
}
