<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Model\Quote\QuoteExtensionData;
use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData as QuoteExtensionDataResource;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Complete order processor pool.
 */
class CompleteOrderPool implements CompleteOrderInterface
{
    public const API_TYPE_SIMPLE = 'simple';

    /**
     * @var QuoteExtensionDataFactory
     */
    private $quoteExtensionDataFactory;

    /**
     * @var QuoteExtensionDataResource
     */
    private $quoteExtensionDataResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CompleteOrderInterface[]
     */
    private $pool;

    /**
     * @param QuoteExtensionDataFactory $quoteExtensionDataFactory
     * @param QuoteExtensionDataResource $quoteExtensionDataResource
     * @param LoggerInterface $logger
     * @param array $pool
     */
    public function __construct(
        QuoteExtensionDataFactory  $quoteExtensionDataFactory,
        QuoteExtensionDataResource $quoteExtensionDataResource,
        LoggerInterface            $logger,
        array                      $pool = []
    ) {
        $this->quoteExtensionDataFactory = $quoteExtensionDataFactory;
        $this->quoteExtensionDataResource = $quoteExtensionDataResource;
        $this->logger = $logger;
        $this->pool = $pool;
    }

    /**
     * @inheritDoc
     */
    public function execute(OrderInterface $order): void
    {
        /** @var QuoteExtensionData $quoteExtensionData */
        $quoteExtensionData = $this->quoteExtensionDataFactory->create();
        $this->quoteExtensionDataResource->load(
            $quoteExtensionData,
            $order->getQuoteId(),
            QuoteExtensionDataResource::QUOTE_ID
        );
        $flowType = $quoteExtensionData->getApiType();

        // TODO: Remove logic around order pool as we only need to run logic here for simple order types
        // For 'default' order types all order complete processing happens in the platform connector
        // TODO: Update naming around simple/non-simple orders to signal it relates to source of truth instead of order type
        if ($flowType === self::API_TYPE_SIMPLE) {
            $processor = $this->pool[$flowType] ?? null;
            if (!($processor instanceof CompleteOrderInterface)) {
                $this->logger->error(
                    __('Failed to find complete processor for order with id="%1"', $order->getEntityId())
                );

                return;
            }

            $processor->execute($order);
        }
    }
}
