<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Api\Data\CartItemInterface;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
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
            $lineItemQty = $this->getLineItemQty($subject);
            if ($lineItemQty !== (int)$qty) {
                $this->client->put(
                    (int)$subject->getQuote()->getStore()->getWebsiteId(),
                    'items',
                    [
                        'platform_id' => (string)$subject->getProduct()->getId(),
                        'line_item_key' => (string)$subject->getItemId(),
                        'quantity' => (int)$qty,
                    ]
                );
            }
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * Get line item qty from Bold Checkout data.
     *
     * @param CartItemInterface $cartItem
     * @return int
     * @throws LocalizedException
     */
    private function getLineItemQty(CartItemInterface $cartItem): int
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        $lineItems = $boldCheckoutData['data']['application_state']['line_items'] ?? [];
        foreach ($lineItems as $lineItem) {
            $lineItemKey = $lineItem['product_data']['line_item_key'] ?? null;
            if ((string)$cartItem->getItemId() === $lineItemKey) {
                return (int)$lineItem['product_data']['quantity'];
            }
        }
        throw new LocalizedException(__('There is no line item with key: %1', $cartItem->getItemId()));
    }
}
