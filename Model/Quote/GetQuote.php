<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\GetQuoteInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Framework\Exception\LocalizedException;

/**
 * Set quote addresses service.
 */
class GetQuote implements GetQuoteInterface
{
    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var LoadAndValidate
     */
    private $loadAndValidate;

    /**
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate
    ) {
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(
        string $shopId,
        int $cartId
    ): ResultInterface {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        $quote->collectTotals();
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
