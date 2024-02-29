<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Resume order on Bold side.
 */
class ResumeOrder
{
    private const RESUME_URL = '/checkout/orders/{{shopId}}/resume';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(
        ClientInterface $client,
    ) {
        $this->client = $client;
    }

    /**
     * Resume order on bold side.
     *
     * @param CartInterface $quote
     * @param string $publicOrderId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function resume(CartInterface $quote, string $publicOrderId): array
    {
        $websiteId = (int)$quote->getStore()->getWebsiteId();
        $body = [
            'public_order_id' => $publicOrderId
        ];

        $orderData = $this->client->post($websiteId, self::RESUME_URL, $body)->getBody();
        $publicOrderId = $orderData['data']['public_order_id'] ?? null;
        if (!$publicOrderId) {
            throw new LocalizedException(__('Cannot resume order'));
        }

        return $orderData;
    }
}
