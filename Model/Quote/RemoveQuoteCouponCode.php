<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\RemoveQuoteCouponCodeInterface;
use Bold\Checkout\Model\Http\Client\Request\Validator\ShopIdValidator;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CouponManagementInterface;

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
     * @param ShopIdValidator $shopIdValidator
     * @param CouponManagementInterface $couponService
     * @param CartRepositoryInterface $cartRepository
     * @param Builder $quoteResultBuilder
     */
    public function __construct(
        ShopIdValidator $shopIdValidator,
        CouponManagementInterface $couponService,
        CartRepositoryInterface $cartRepository,
        Builder $quoteResultBuilder
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->cartRepository = $cartRepository;
        $this->shopIdValidator = $shopIdValidator;
    }

    /**
     * @inheritdoc
     */
    public function removeCoupon(string $shopId, int $cartId): ResultInterface
    {
        try {
            $quote = $this->cartRepository->getActive($cartId);
            $this->shopIdValidator->validate($shopId, $quote->getStoreId());
            $this->couponService->remove($cartId);
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
