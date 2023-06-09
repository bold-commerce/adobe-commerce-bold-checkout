<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Model\Quote;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\Quote;

/**
 * Send remove item request to Bold.
 */
class RemoveCartItemPlugin
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
     * Send remove item request to Bold.
     *
     * @param Quote $subject
     * @param Quote $result
     * @param int $itemId
     * @return Quote
     */
    public function afterRemoveItem(Quote $subject, Quote $result, $itemId): Quote
    {
        if (!$this->checkoutSession->getBoldCheckoutData()) {
            return $result;
        }
        try {
            $lineItem = $this->getLineItem((int)$itemId);
            $this->client->delete(
                (int)$result->getStore()->getWebsiteId(),
                'items',
                [
                    'platform_id' => $lineItem['product_data']['variant_id'],
                    'line_item_key' => (string)$itemId,
                    'quantity' => $lineItem['product_data']['quantity'],
                ]
            );
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }

    /**
     * Get line item qty from Bold Checkout data.
     *
     * @param int $cartItemId
     * @return array
     * @throws LocalizedException
     */
    private function getLineItem(int $cartItemId): array
    {
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        $lineItems = $boldCheckoutData['data']['application_state']['line_items'] ?? [];
        foreach ($lineItems as $lineItem) {
            $lineItemKey = $lineItem['product_data']['line_item_key'] ?? null;
            if ((string)$cartItemId === $lineItemKey) {
                return $lineItem;
            }
        }
        throw new LocalizedException(__('There is no line item with key: %1', $cartItemId));
    }
}
