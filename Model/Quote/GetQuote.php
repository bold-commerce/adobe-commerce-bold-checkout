<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\GetQuoteInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var Cart
     */
    private $cart;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param ShopIdValidator $shopIdValidator
     * @param Builder $quoteResultBuilder
     * @param Cart $cart used for the backward compatibility with earlier versions of Magento.
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        ShopIdValidator $shopIdValidator,
        Builder $quoteResultBuilder,
        Cart $cart,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cart = $cart;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
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
            $this->checkoutSession->replaceQuote($quote);
            $this->cart->setQuote($quote);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
        } catch (LocalizedException $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        $quote->collectTotals();
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
