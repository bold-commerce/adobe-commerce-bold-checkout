<?php

declare(strict_types=1);

namespace Bold\Checkout\Model\Order;

use Bold\Checkout\Api\Http\ClientInterface;
use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Order\OrderExtensionData as OrderExtensionDataResource;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/**
 * Complete order processor pool.
 */
class CompleteOrderPool implements CompleteOrderInterface
{

    /**
     * @var QuoteExtensionDataFactory
     */
    private $quoteExtensionDataFactory;

    /**
     * @var QuoteExtensionData
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
     * @param ClientInterface $client
     * @param OrderExtensionDataFactory $orderExtensionDataFactory
     * @param OrderExtensionDataResource $orderExtensionDataResource
     * @param QuoteExtensionDataFactory $quoteExtensionDataFactory
     * @param QuoteExtensionData $quoteExtensionDataResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteExtensionDataFactory $quoteExtensionDataFactory,
        QuoteExtensionData        $quoteExtensionDataResource,
        LoggerInterface           $logger,
        array                     $pool = []
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
        $quoteExtensionData = $this->quoteExtensionDataFactory->create();
        $this->quoteExtensionDataResource->load(
            $quoteExtensionData,
            $order->getQuoteId(), QuoteExtensionData::QUOTE_ID
        );
        $flowType = $quoteExtensionData->getFlowType();
        $processor = $this->pool[InitOrderFromQuote::FLOW_TYPE_DEFAULT] ?? null;
        if (in_array($flowType, array_keys($this->pool), true)) {
            $processor = $this->pool[$flowType];
        }
        if ($processor instanceof CompleteOrderInterface) {
            $processor->execute($order);
        } else {
            $this->logger->error(
                __('Failed to find complete processor for order with id="%1"', $order->getEntityId())
            );
        }
    }
}
