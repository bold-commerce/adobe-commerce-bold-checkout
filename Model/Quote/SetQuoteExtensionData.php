<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Model\ResourceModel\Quote\QuoteExtensionData as QuoteExtensionDataResource;
use Exception;
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
     * @param array $data
     * @return void
     */
    public function execute(int $quoteId, array $data): void
    {
        try {
            $quoteExtensionData = $this->quoteExtensionDataFactory->create();
            $this->quoteExtensionDataResource->load(
                $quoteExtensionData,
                $quoteId,
                QuoteExtensionDataResource::QUOTE_ID
            );
            if (!$quoteExtensionData->getId()) {
                $quoteExtensionData->setQuoteId($quoteId);
            }
            $updated = false;
            foreach ($data as $key => $value) {
                if ($quoteExtensionData->getData($key) !== $value) {
                    $quoteExtensionData->setData($key, $value);
                    $updated = true;
                }
            }
            if ($updated) {
                $this->quoteExtensionDataResource->save($quoteExtensionData);
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
