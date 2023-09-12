<?php
declare(strict_types=1);

namespace Bold\Checkout\Observer\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\Address\Converter;
use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Place order on Bold side observer.
 */
class ProcessOrderObserver implements ObserverInterface
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
     * @var Converter
     */
    private $addressConverter;

    /**
     * @param Session $checkoutSession
     * @param ClientInterface $client
     * @param Converter $addressConverter
     */
    public function __construct(
        Session $checkoutSession,
        ClientInterface $client,
        Converter $addressConverter
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->client = $client;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Place order on Bold side.
     *
     * @param Observer $observer
     * @return void
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $boldCheckoutData = $this->checkoutSession->getBoldCheckoutData();
        if (!$boldCheckoutData) {
            return;
        }
        if ($order->getPayment()->getMethod() !== 'bold') {
            return;
        }
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->syncAddress($order, $websiteId);
        $subRequests = [
            'sub_requests' => [
                [
                    'method' => 'POST',
                    'endpoint' => '/taxes',
                    'payload' => new \stdClass(),
                ],
                [
                    'method' => 'POST',
                    'endpoint' => '/process_order',
                    'payload' => new \stdClass(),
                ],
            ],
        ];
        $boldOrder = $this->client->post($websiteId, 'batch', $subRequests);
        if ($boldOrder->getErrors()) {
            throw new LocalizedException(__('Something went wrong. Please try to place the order again.'));
        }
    }

    /**
     * Synchronize shipping address with Bold in case order is not virtual. Update billing address otherwise.
     *
     * @param OrderInterface $order
     * @param int $websiteId
     * @return void
     * @throws \Exception
     */
    private function syncAddress(OrderInterface $order, int $websiteId): void
    {
        if (!$order->getIsVirtual()) {
            $addressPayload = $this->addressConverter->convert($order->getShippingAddress());
            $this->client->post($websiteId, 'addresses/shipping', $addressPayload);
            return;
        }
        $addressPayload = $this->addressConverter->convert($order->getBillingAddress());
        $this->client->post($websiteId, 'addresses/billing', $addressPayload);
    }
}
