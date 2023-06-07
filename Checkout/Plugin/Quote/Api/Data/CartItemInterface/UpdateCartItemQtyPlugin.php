<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Api\Data\CartItemInterface;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\CartItemInterface;

/**
 * Send update qty request to Bold plugin.
 */
class UpdateCartItemQtyPlugin
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param Session $checkoutSession
     * @param ClientInterface $client
     */
    public function __construct(Session $checkoutSession, ClientInterface $client)
    {
        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
    }

    /**
     * Send update qty to Bold.
     *
     * @param CartItemInterface $subject
     * @param CartItemInterface $result
     * @param float $qty
     * @return CartItemInterface
     */
    public function afterSetQty(CartItemInterface $subject, CartItemInterface $result, $qty): CartItemInterface
    {
        if (!$this->checkoutSession->getBoldCheckoutData()) {
            return $result;
        }
        try {
            $this->client->put(
                (int)$subject->getQuote()->getStore()->getWebsiteId(),
                'items',
                [
                    'line_item_key' => (string)$subject->getItemId(),
                    'quantity' => (int)$qty,
                ]
            );
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }
}
