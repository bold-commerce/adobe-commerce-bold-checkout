<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Model\QuoteManagement;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Process order on Bold side.
 */
class ProcessOrderPlugin
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
    public function __construct(
        ClientInterface $client,
        Session $checkoutSession
    ) {
        $this->client = $client;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Place order on Bold side.
     *
     * @param QuoteManagement $subject
     * @param OrderInterface $result
     * @param CartInterface $quote
     * @return OrderInterface
     * @throws \Exception
     */
    public function afterSubmit(QuoteManagement $subject, OrderInterface $result, CartInterface $quote)
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return $result;
        }
        if ($quote->getPayment()->getMethod() !== 'bold') {
            return $result;
        }
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $order = $this->client->post($websiteId, 'process_order', []);
        if ($order->getErrors()) {
            throw new LocalizedException(__('Could not process order with Bold.'));
        }
        return $result;
    }
}
