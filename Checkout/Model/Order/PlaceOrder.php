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
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Order\OrderExtensionDataFactory;
use Bold\Checkout\Model\Order\PlaceOrder\CreateOrderFromPayload;
use Bold\Checkout\Model\Order\PlaceOrder\ProcessOrder;
use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order;

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
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

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
     * @param ShopIdValidator $shopIdValidator
     * @param OrderPayloadValidator $orderPayloadValidator
     * @param Order $orderResource
     * @param CartRepositoryInterface $cartRepository
     * @param ResultInterfaceFactory $responseFactory
     * @param ErrorInterfaceFactory $errorFactory
     * @param ConfigInterface $config
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        OrderPayloadValidator $orderPayloadValidator,
        CartRepositoryInterface $cartRepository,
        ResultInterfaceFactory $responseFactory,
        ErrorInterfaceFactory $errorFactory,
        ConfigInterface $config,
        CreateOrderFromPayload $createOrderFromPayload,
        ProcessOrder $processOrder
    ) {
        $this->responseFactory = $responseFactory;
        $this->errorFactory = $errorFactory;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->orderPayloadValidator = $orderPayloadValidator;
        $this->config = $config;
        $this->createOrderFromPayload = $createOrderFromPayload;
        $this->processOrder = $processOrder;
    }

    /**
     * @inheritDoc
     */
    public function place(string $shopId, OrderDataInterface $orderPayload): ResultInterface
    {
        try {
            $this->orderPayloadValidator->validate($orderPayload);
            $quote = $this->cartRepository->get($orderPayload->getQuoteId());
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->getValidationErrorResponse($e->getMessage());
        }
        try {
            $websiteId = $quote->getStore()->getWebsiteId();
            $order = $this->config->isSelfHostedCheckoutEnabled($websiteId)
                ? $this->processOrder->process($orderPayload)
                : $this->createOrderFromPayload->createOrder($orderPayload, $quote);
        } catch (Exception $e) {
            return $this->getErrorResponse($e->getMessage());
        }
        return $this->getSuccessResponse($order);
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
     * Build error response.
     *
     * @param string $message
     * @return ResultInterface
     */
    private function getErrorResponse(string $message): ResultInterface
    {
        return $this->responseFactory->create(
            [
                'errors' => [
                    $this->errorFactory->create(
                        [
                            'message' => $message,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * Build order response
     *
     * @param OrderInterface $order
     * @return ResultInterface
     */
    private function getSuccessResponse(OrderInterface $order): ResultInterface
    {
        return $this->responseFactory->create(
            [
                'order' => $order,
            ]
        );
    }
}
