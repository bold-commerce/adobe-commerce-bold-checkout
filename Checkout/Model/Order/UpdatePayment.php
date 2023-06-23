<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\Order\Payment\RequestInterface;
use Bold\Checkout\Api\Data\Order\Payment\ResultInterface;
use Bold\Checkout\Api\Data\Order\Payment\ResultInterfaceFactory;
use Bold\Checkout\Api\Order\UpdatePaymentInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\Order\PlaceOrder\CreateInvoice;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrderPayment;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionResource;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Update order payment and create invoice service.
 */
class UpdatePayment implements UpdatePaymentInterface
{
    /**
     * @var ResultInterfaceFactory
     */
    private $responseFactory;

    /**
     * @var ErrorInterfaceFactory
     */
    private $errorFactory;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var \Bold\Checkout\Model\Order\OrderExtensionDataFactory
     */
    private $orderExtensionDataFactory;

    /**
     * @var OrderExtensionResource
     */
    private $orderExtensionDataResource;

    /**
     * @var CreateInvoice
     */
    private $createInvoice;

    /**
     * @var ProcessOrderPayment
     */
    private $processOrderPayment;

    /**
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ShopIdValidator $shopIdValidator
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionResource $orderExtensionDataResource
     * @param CreateInvoice $createInvoice
     * @param ProcessOrderPayment $processOrderPayment
     */
    public function __construct(
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        ShopIdValidator $shopIdValidator,
        OrderExtensionDataFactory $orderExtensionDataFactory,
        OrderExtensionResource $orderExtensionDataResource,
        CreateInvoice $createInvoice,
        ProcessOrderPayment $processOrderPayment
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->shopIdValidator = $shopIdValidator;
        $this->orderExtensionDataFactory = $orderExtensionDataFactory;
        $this->orderExtensionDataResource = $orderExtensionDataResource;
        $this->createInvoice = $createInvoice;
        $this->processOrderPayment = $processOrderPayment;
    }

    /**
     * @inheritDoc
     */
    public function update(string $shopId, RequestInterface $payment): ResultInterface
    {
        try {
            $this->validateRequest($payment);
            $order = $payment->getPayment()->getOrder();
            $websiteId = (int)$order->getStore()->getWebsiteId();
            $this->shopIdValidator->validate($shopId, $websiteId);
        } catch (LocalizedException $e) {
            return $this->getValidationErrorResponse($e->getMessage());
        }
        if ($this->isDelayedCapture($order)) {
            return $this->responseFactory->create(
                [
                    'payment' => $order->getPayment(),
                ]
            );
        }
        try {
            $this->processOrderPayment->process(
                $order,
                $payment->getPayment(),
                $payment->getTransaction()
            );
            $this->createInvoice->create($order);
        } catch (Exception $e) {
            return $this->responseFactory->create(
                [
                    'errors' => [
                        $this->errorFactory->create(
                            [
                                'message' => $e->getMessage(),
                            ]
                        ),
                    ],
                ]
            );
        }
        return $this->responseFactory->create(
            [
                'payment' => $order->getPayment(),
            ]
        );
    }

    /**
     * Build validation error response.
     *
     * @param string $message
     * @return ResultInterface
     */
    private function getValidationErrorResponse(string $message): ResultInterface
    {
        return $this->responseFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $message,
                            'code' => 422,
                            'type' => 'server.validation_error',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Retrieve is order using delayed payment capture.
     *
     * @param OrderInterface $order
     * @return int
     */
    private function isDelayedCapture(OrderInterface $order): int
    {
        /** @var OrderExtensionData $orderExtData */
        $orderExtData = $this->orderExtensionDataFactory->create();
        $this->orderExtensionDataResource->load(
            $orderExtData,
            $order->getId(),
            OrderExtensionResource::ORDER_ID
        );
        return $orderExtData->getIsDelayedCapture();
    }

    /**
     * @param RequestInterface $payment
     * @return void
     * @throws LocalizedException
     */
    public function validateRequest(RequestInterface $payment): void
    {
        if (!$payment->getPayment()) {
            throw new LocalizedException(__('Provided request has no payment.'));
        }
        if (!$payment->getPayment()->getOrder()) {
            throw new LocalizedException(__('Provided payment has no order.'));
        }
        if (!$payment->getTransaction()) {
            throw new LocalizedException(__('Provided payment has no transaction.'));
        }
    }
}
