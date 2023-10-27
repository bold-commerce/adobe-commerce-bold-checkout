<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\RemoveQuoteCouponCodeInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Quote\Api\CouponManagementInterface;

/**
 * Remove quote coupon code service.
 */
class RemoveQuoteCouponCode implements RemoveQuoteCouponCodeInterface
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
     * @var CouponManagementInterface
     */
    private $couponService;

    /**
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     * @param CouponManagementInterface $couponService
     */
    public function __construct(
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate,
        CouponManagementInterface $couponService
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
    }

    /**
     * @inheritdoc
     */
    public function removeCoupon(string $shopId, int $cartId, string $couponCode = null): ResultInterface
    {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
            $this->couponService->remove($quote->getId());
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
