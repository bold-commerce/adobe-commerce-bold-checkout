<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order\Shipment;

use Bold\Checkout\Api\Http\ClientInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

/**
 * Fulfill order items on bold side observer.
 */
class FulfillOrderItemsObserver implements ObserverInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Fulfill order items on bold side after order has been shipped.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        $publicId = $order->getExtensionAttributes()->getPublicId();
        if (!$publicId) {
            return;
        }
        $url = sprintf('/checkout/orders/{{shopId}}/%s/line_items', $publicId);
        $itemsToFulfill = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getChildrenItems()) {
                continue;
            }
            $fulfilledQty = $this->getFulfilledQty($item);
            if (!$fulfilledQty) {
                continue;
            }
            $itemsToFulfill[] = [
                'line_item_key' => (string)$item->getQuoteItemId(),
                'fulfilled_quantity' => $fulfilledQty,
            ];
        }
        if (!$itemsToFulfill) {
            return;
        }
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $body = ['line_items' => $itemsToFulfill];
        try {
            $this->client->patch($websiteId, $url, $body);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Retrieve invoiced and shipped qty for non-virtual and invoiced qty for virtual items.
     *
     * @param OrderItemInterface $item
     * @return int
     */
    private function getFulfilledQty(OrderItemInterface $item): int
    {
        $item = $item->getParentItem() ?: $item;
        return (int)$item->getQtyShipped() - (int)$item->getOrigData('qty_shipped');
    }
}
