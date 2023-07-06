<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\GetQuoteInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;

/**
 * Set quote addresses service.
 */
class GetQuote implements GetQuoteInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param Builder $quoteResultBuilder
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        Builder $quoteResultBuilder
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->quoteResultBuilder = $quoteResultBuilder;
    }

    /**
     * @inheritDoc
     */
    public function getQuote(
        string $shopId,
        int $cartId
    ): ResultInterface {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
