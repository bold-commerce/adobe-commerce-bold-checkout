<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Quote\Model\Quote;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Checkout\Model\Session;
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
            $this->client->delete(
                (int)$result->getStore()->getWebsiteId(),
                'items',
                [
                    'line_item_key' => (string)$itemId,
                    'quantity' => 0,
                ]
            );
        } catch (\Exception $e) {
            return $result;
        }
        return $result;
    }
}
