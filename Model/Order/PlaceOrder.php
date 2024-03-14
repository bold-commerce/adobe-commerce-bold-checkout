<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Data\Http\Client\Response\ErrorInterfaceFactory;
use Bold\Checkout\Api\Data\PlaceOrder\Request\OrderDataInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterface;
use Bold\Checkout\Api\Data\PlaceOrder\ResultInterfaceFactory;
use Bold\Checkout\Api\PlaceOrderInterface;
use Bold\Checkout\Model\ConfigInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\OrderPayloadValidator;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromPayload;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrder;
use Bold\Checkout\Model\Order\PlaceOrder\Progress;
use Bold\Checkout\Model\Quote\LoadAndValidate;
use Exception;
use Magento\Framework\Exception\LocalizedException;

/**
 * Place magento order with bold payment service.
 */
class PlaceOrder implements PlaceOrderInterface
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
     * @var OrderPayloadValidator
     */
    private $orderPayloadValidator;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var CreateOrderFromPayload
     */
    private $createOrderFromPayload;

    /**
     * @var ProcessOrder
     */
    private $processOrder;

    /**
     * @var Progress
     */
    private $progress;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @param OrderPayloadValidator $orderPayloadValidator
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ConfigInterface $config
     * @param CreateOrderFromPayload $createOrderFromPayload
     * @param ProcessOrder $processOrder
     * @param Progress $progress
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        OrderPayloadValidator $orderPayloadValidator,
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        ConfigInterface $config,
        CreateOrderFromPayload $createOrderFromPayload,
        ProcessOrder $processOrder,
        Progress $progress,
        LoadAndValidate $loadAndValidate
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->orderPayloadValidator = $orderPayloadValidator;
        $this->config = $config;
        $this->createOrderFromPayload = $createOrderFromPayload;
        $this->processOrder = $processOrder;
        $this->progress = $progress;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritDoc
     */
    public function place(string $shopId, OrderDataInterface $order): ResultInterface
    {
        if ($this->progress->isInProgress($order)) {
            return $this->getValidationErrorResponse(
                __('Order for cart id: "%1" already in progress.', $order->getQuoteId())->render()
            );
        }
        $this->progress->start($order);
        try {
            $this->orderPayloadValidator->validate($order);
            $quote = $this->loadAndValidate->load($shopId, $order->getQuoteId());
        } catch (LocalizedException $e) {
            $this->progress->stop($order);
            return $this->getValidationErrorResponse($e->getMessage());
        }
        try {
            $websiteId = (int)$quote->getStore()->getWebsiteId();
            $magentoOrder = $this->config->isCheckoutTypeSelfHosted($websiteId)
                ? $this->processOrder->process($order)
                : $this->createOrderFromPayload->createOrder($order, $quote);
        } catch (Exception $e) {
            $this->progress->stop($order);
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
        $this->progress->stop($order);
        return $this->responseFactory->create(
            [
                'order' => $magentoOrder,
            ]
        );
    }

    /**
     * Build validation error response.
     *
     * @param string $message
     * @return Bold\Checkout\Api\Data\PlaceOrder\ResultInterface
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

}
