<?php

namespace Bold\Checkout\Model\Order\InitOrderFromQuote;

use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

/**
 * Process bold order data.
 */
class ProcessBoldOrderData implements OrderDataProcessorInterface
{
    /**
     * @var Session
     */
    private $checkoutSession;

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
     * @param Session $checkoutSession
     * @param QuoteExtensionDataFactory $quoteExtensionDataFactory
     * @param QuoteExtensionData $quoteExtensionDataResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Session                   $checkoutSession,
        QuoteExtensionDataFactory $quoteExtensionDataFactory,
        QuoteExtensionData        $quoteExtensionDataResource,
        LoggerInterface           $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteExtensionDataFactory = $quoteExtensionDataFactory;
        $this->quoteExtensionDataResource = $quoteExtensionDataResource;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function process(array $data, CartInterface $quote): array
    {
        try {
            $this->checkoutSession->setBoldCheckoutData($data);
            $quoteExtensionData = $this->quoteExtensionDataFactory->create();
            $quoteExtensionData->setQuoteId((int)$quote->getId());
            $quoteExtensionData->setOrderCreated(true);
            $this->quoteExtensionDataResource->save($quoteExtensionData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $data;
    }
}
