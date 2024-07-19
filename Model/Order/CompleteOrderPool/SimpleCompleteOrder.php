<?php

namespace Bold\Checkout\Model\Order\CompleteOrderPool;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\CompleteOrderInterface;
use Bold\Checkout\Model\Order\GetOrderPublicId;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Simple complete order processor.
 */
class SimpleCompleteOrder implements CompleteOrderInterface
{
    private const COMPLETE_ORDER_URL = 'checkout_sidekick/{{shopId}}/order/%s/state';

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
        $url = sprintf(self::COMPLETE_ORDER_URL, $publicOrderId);
        $params = [
            'state' => 'order_complete',
            'platform_order_id' => $order->getEntityId(),
            'platform_friendly_id' => $order->getIncrementId(),
        ];
        $response = $this->client->put($websiteId, $url, $params);
        if ($response->getStatus() !== 201) {
            $this->logger->error(__('Failed to complete order with id="%1"', $order->getEntityId()));
        }
    }
}
