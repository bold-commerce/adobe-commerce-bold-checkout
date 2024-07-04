<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Mark the Order as created on the Bold side.
 */
class CompleteOrder
{
    private const COMPLETE_URL = 'checkout/orders/{{shop_id}}/%s/complete';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionDataResource
     */
    private $orderExtensionDataResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ClientInterface $client
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ClientInterface            $client,
        OrderExtensionDataFactory  $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        LoggerInterface $logger
    ) {
        $this->client = $client;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->logger = $logger;
    }

    /**
     * Mark the Order as created on the Bold side.
     *
     * @param OrderInterface $order
     * @return void
     * @throws LocalizedException
     */
    public function execute(OrderInterface $order): void
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $publicOrderId = $this->getOrderPublicId($order);
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

    /**
     * Retrieve order public id.
     *
     * @param OrderInterface $order
     * @return string
     * @throws LocalizedException
     */
    private function getOrderPublicId(OrderInterface $order): string
    {
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $this->orderExtensionDataResource->load($orderExtensionData, $order->getId(), OrderExtensionData::ORDER_ID);
        if (!$orderExtensionData->getPublicId()) {
            throw new LocalizedException(__('Order public id is not set.'));
        }

        return $orderExtensionData->getPublicId();
    }
}
