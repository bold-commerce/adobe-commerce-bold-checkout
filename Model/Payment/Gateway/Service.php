<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Payment\Gateway;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Bold gateway service.
 */
class Service
{
    public const CODE = 'bold';
    public const CANCEL = 'cancel';
    public const VOID = 'void';
    private const CAPTURE_FULL_URL = 'checkout/orders/{{shopId}}/%s/payments/capture/full';
    private const CAPTURE_PARTIALLY_URL = 'checkout/orders/{{shopId}}/%s/payments/capture';
    private const REFUND_FULL_URL = 'checkout/orders/{{shopId}}/%s/refunds/full';
    private const REFUND_PARTIALLY_URL = 'checkout/orders/{{shopId}}/%s/refunds';
    private const CANCEL_URL = 'checkout/orders/{{shopId}}/%s/cancel';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionData
     */
    private $orderExtensionDataResource;

    /**
     * @param ClientInterface $httpClient
     * @param Random $random
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionData $orderExtensionDataResource
     */
    public function __construct(
        ClientInterface $httpClient,
        Random $random,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionData $orderExtensionDataResource
    ) {
        $this->httpClient = $httpClient;
        $this->random = $random;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
    }

    /**
     * Capture a payment for the full order amount.
     *
     * @param OrderInterface $order
     * @return string
     * @throws Exception
     */
    public function captureFull(OrderInterface $order): string
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->keepTransactionAdditionalData($order);
        $body = [
            'reauth' => true,
            'idempotent_key' => $this->random->getRandomString(10),
        ];
        $url = sprintf(self::CAPTURE_FULL_URL, $this->getOrderPublicId($order));
        return $this->sendCaptureRequest($websiteId, $url, $body);
    }

    /**
     * Capture a payment by an arbitrary amount.
     *
     * @param OrderInterface $order
     * @param float $amount
     * @return string
     * @throws Exception
     */
    public function capturePartial(OrderInterface $order, float $amount): string
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->keepTransactionAdditionalData($order);
        $body = [
            'reauth' => true,
            'amount' => $amount * 100,
            'idempotent_key' => $this->random->getRandomString(10),
        ];
        $url = sprintf(self::CAPTURE_PARTIALLY_URL, $this->getOrderPublicId($order));
        return $this->sendCaptureRequest($websiteId, $url, $body);
    }

    /**
     * Cancel order via bold.
     *
     * @param OrderInterface $order
     * @param string $operation
     * @return void
     * @throws Exception
     */
    public function cancel(OrderInterface $order, string $operation = self::CANCEL)
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->keepTransactionAdditionalData($order);
        $url = sprintf(self::CANCEL_URL, $this->getOrderPublicId($order));
        $body = [
            'reason' => $operation === self::CANCEL
                ? __('Order has been canceled.')
                : __('Order payment has been voided.'),
        ];
        $result = $this->httpClient->post($websiteId, $url, $body);
        if ($result->getErrors()) {
            $message = isset(current($result->getErrors())['message'])
                ? __(current($result->getErrors())['message'])
                : __('Cannot void the payment.');
            throw new LocalizedException($message);
        }
        $body = $result->getBody();
        if (!isset($body['data']['application_state'])) {
            $message = $operation === self::CANCEL
                ? __('Cannot cancel order. Please try again later.')
                : __('Cannot void the payment. Please try again later.');
            throw new LocalizedException($message);
        }
    }

    /**
     * Refund a payment for the full order amount.
     *
     * @param OrderInterface $order
     * @return string
     * @throws Exception
     */
    public function refundFull(OrderInterface $order): string
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->keepTransactionAdditionalData($order);
        $body = [
            'email_notification' => false,
            'reason' => 'Magento credit memo created.',
        ];
        $url = sprintf(self::REFUND_FULL_URL, $this->getOrderPublicId($order));
        return $this->sendRefundRequest($websiteId, $url, $body);
    }

    /**
     * Refund a payment by an arbitrary amount.
     *
     * @param OrderInterface $order
     * @param float $amount
     * @return string
     * @throws Exception
     */
    public function refundPartial(OrderInterface $order, float $amount): string
    {
        $websiteId = (int)$order->getStore()->getWebsiteId();
        $this->keepTransactionAdditionalData($order);
        $body = [
            'email_notification' => false,
            'reason' => 'Magento credit memo created.',
            'amount' => $amount * 100,
        ];
        $url = sprintf(self::REFUND_PARTIALLY_URL, $this->getOrderPublicId($order));
        return $this->sendRefundRequest($websiteId, $url, $body);
    }

    /**
     * Perform capture api call.
     *
     * @param int $websiteId
     * @param string $url
     * @param array $body
     * @return string
     * @throws Exception
     */
    private function sendCaptureRequest(int $websiteId, string $url, array $body): string
    {
        $result = $this->httpClient->post($websiteId, $url, $body);
        if ($result->getErrors()) {
            $message = isset(current($result->getErrors())['message'])
                ? __(current($result->getErrors())['message'])
                : __('Cannot capture the order.');
            throw new LocalizedException($message);
        }
        $body = $result->getBody();
        if (!isset($body['data']['capture']['transactions'])) {
            throw new LocalizedException(__('Cannot capture the order.'));
        }
        $transaction = current($body['data']['capture']['transactions']);
        return $transaction['transaction_id'];
    }

    /**
     * Perform refund api call.
     *
     * @param int $websiteId
     * @param string $url
     * @param array $body
     * @return string
     * @throws Exception
     */
    private function sendRefundRequest(int $websiteId, string $url, array $body): string
    {
        $result = $this->httpClient->post($websiteId, $url, $body);
        if ($result->getErrors()) {
            $message = isset(current($result->getErrors())['message'])
                ? __(current($result->getErrors())['message'])
                : __('Cannot refund the order.');
            throw new LocalizedException($message);
        }
        $body = $result->getBody();
        if (!isset($body['data']['refund']['transaction_details'])) {
            throw new LocalizedException(__('Cannot refund the order.'));
        }
        $transactionDetails = current($body['data']['refund']['transaction_details']);
        return $transactionDetails['transaction_number'];
    }

    /**
     * Keep transaction additional information for future transactions.
     *
     * @param OrderInterface $order
     * @return void
     */
    private function keepTransactionAdditionalData(OrderInterface $order): void
    {
        $lastTransaction = $order->getPayment()->getAuthorizationTransaction();
        if (!$lastTransaction) {
            return;
        }
        $transactionAdditionalInfo = $lastTransaction->getAdditionalInformation() ?: [];
        foreach ($transactionAdditionalInfo as $key => $value) {
            $order->getPayment()->setTransactionAdditionalInfo($key, $value);
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
        $this->orderExtensionDataResource->load(
            $orderExtensionData,
            $order->getId(),
            OrderExtensionData::ORDER_ID
        );
        if (!$orderExtensionData->getPublicId()) {
            throw new LocalizedException(__('Order public id is not set.'));
        }
        return $orderExtensionData->getPublicId();
    }
}
