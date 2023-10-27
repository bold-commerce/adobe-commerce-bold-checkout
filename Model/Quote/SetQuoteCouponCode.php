<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Quote\Api\CouponManagementInterface;

/**
 * Set quote coupon code service.
 */
class SetQuoteCouponCode implements SetQuoteCouponCodeInterface
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
     * @param CouponManagementInterface $couponService
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     */
    public function __construct(
        CouponManagementInterface $couponService,
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate
    ) {
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
        $this->couponService = $couponService;
    }

    /**
     * @inheritdoc
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface
    {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
            $this->couponService->set($quote->getId(), $couponCode);
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
