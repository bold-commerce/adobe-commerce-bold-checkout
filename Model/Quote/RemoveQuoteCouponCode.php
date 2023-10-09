<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\RemoveQuoteCouponCodeInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Remove quote coupon code service.
 */
class RemoveQuoteCouponCode implements RemoveQuoteCouponCodeInterface
{
    /**
     * @var CouponManagementInterface
     */
    private $couponService;

    /**
     * @var Builder
     */
    private $quoteResultBuilder;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ShopIdValidator
     */
    private $shopIdValidator;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @param ShopIdValidator $shopIdValidator
     * @param CouponManagementInterface $couponService
     * @param CartRepositoryInterface $cartRepository
     * @param Builder $quoteResultBuilder
     * @param StoreManagerInterface $storeManager
     * @param Session $checkoutSession
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        CouponManagementInterface $couponService,
        CartRepositoryInterface $cartRepository,
        Builder $quoteResultBuilder,
        StoreManagerInterface $storeManager,
        Session $checkoutSession
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function removeCoupon(string $shopId, int $cartId, string $couponCode = null): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->checkoutSession->replaceQuote($quote);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->storeManager->setCurrentStore($quote->getStoreId());
            $this->storeManager->getStore()->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());
            $this->couponService->remove($cartId);
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
