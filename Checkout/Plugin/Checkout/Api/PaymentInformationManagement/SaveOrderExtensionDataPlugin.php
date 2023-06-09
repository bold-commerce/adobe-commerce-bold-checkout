<?php
declare(strict_types=1);

namespace Bold\Checkout\Plugin\Checkout\Api\PaymentInformationManagement;

use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;

/**
 * Save order extension data.
 */
class SaveOrderExtensionDataPlugin
{
    /**
     * @var OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionDataResource
     */
    private $orderExtensionDataResource;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     */
    public function __construct(
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionDataResource $orderExtensionDataResource,
        Session $checkoutSession
    ) {
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param PaymentInformationManagementInterface $subject
     * @param int $orderId
     * @return int
     */
    public function afterSavePaymentInformationAndPlaceOrder(
        PaymentInformationManagementInterface $subject,
        $orderId
    ):int {
        $publicOrderId = $this->checkoutSession->getBoldCheckoutData()['data']['public_order_id'] ?? null;
        $this->checkoutSession->setBoldCheckoutData(null);
        if (!$publicOrderId) {
            return (int)$orderId;
        }
        $orderExtensionData = $this->orderExtensionDataFactory->create();
        $orderExtensionData->setOrderId((int)$orderId);
        $orderExtensionData->setPublicId($publicOrderId);
        $orderExtensionData->setFulfillmentStatus('pending');
        $orderExtensionData->setFinancialStatus('pending');
        try {
            $this->orderExtensionDataResource->save($orderExtensionData);
        } catch (\Exception $e) {
            return (int)$orderId;
        }
        return (int)$orderId;
    }
}
