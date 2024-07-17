<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order\CompleteOrderPool;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\CompleteOrderInterface;
use Bold\Checkout\Model\Order\GetOrderPublicId;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Default complete order processor.
 */
class DefaultCompleteOrder implements CompleteOrderInterface
{
    private const COMPLETE_URL = 'checkout/orders/{{shop_id}}/%s/complete';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var GetOrderPublicId
     */
    private $getOrderPublicId;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientInterface $client
     * @param GetOrderPublicId $getOrderPublicId
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface            $client,
        GetOrderPublicId $getOrderPublicId,
        LoggerInterface            $logger
    ) {
        $this->client = $client;
        $this->getOrderPublicId = $getOrderPublicId;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute(OrderInterface $order): void
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $publicOrderId = $this->getOrderPublicId->execute($order);
        $orderId = $order->getEntityId();
        $body = [
            'platform_order_id' => $orderId,
        ];
        $url = sprintf(self::COMPLETE_URL, $publicOrderId);
        $response = $this->client->post($websiteId, $url, $body);
        if ($response->getStatus() !== 200) {
            $this->logger->error(__('Failed to complete order with id="%1"', $orderId));
        }
    }
}
