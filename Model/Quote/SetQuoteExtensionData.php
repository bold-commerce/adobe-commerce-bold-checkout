<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\Quote\QuoteExtensionDataFactory;
use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData as QuoteExtensionDataResource;
use Psr\Log\LoggerInterface;

/**
 * Set quote extension data.
 */
class SetQuoteExtensionData
{
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
     * @param QuoteExtensionDataFactory $quoteExtensionDataFactory
     * @param QuoteExtensionDataResource $quoteExtensionDataResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteExtensionDataFactory $quoteExtensionDataFactory,
        QuoteExtensionDataResource $quoteExtensionDataResource,
        LoggerInterface $logger
    ) {
        $this->quoteExtensionDataFactory = $quoteExtensionDataFactory;
        $this->quoteExtensionDataResource = $quoteExtensionDataResource;
        $this->logger = $logger;
    }

    /**
     * Set quote extension data.
     *
     * @param int $quoteId
     * @param bool $orderCreated
     * @return void
     */
    public function execute(int $quoteId, bool $orderCreated = false): void
    {
        try {
            $quoteExtensionData = $this->quoteExtensionDataFactory->create();
            $quoteExtensionData->setQuoteId($quoteId);
            $quoteExtensionData->setOrderCreated($orderCreated);
            $this->quoteExtensionDataResource->save($quoteExtensionData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
