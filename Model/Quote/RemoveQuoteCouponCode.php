<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\RemoveQuoteCouponCodeInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Quote\Api\CouponManagementInterface;
use Magento\Quote\Model\ResourceModel\Quote;

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
     * @var Quote
     */
    private $quoteResource;

    /**
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     * @param CouponManagementInterface $couponService
     * @param Quote $quoteResource
     */
    public function __construct(
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate,
        CouponManagementInterface $couponService,
        Quote $quoteResource
    ) {
        $this->couponService = $couponService;
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
        $this->quoteResource = $quoteResource;
    }

    /**
     * @inheritdoc
     */
    public function removeCoupon(string $shopId, int $cartId, string $couponCode = null): ResultInterface
    {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
            $this->couponService->remove($quote->getId());
            $quote->getShippingAddress()->setCollectShippingRates(true);
            try {
                $quote->setCouponCode('');
                $quote->collectTotals();
                $this->quoteResource->save($quote);
            } catch (\Exception $e) {
                throw new CouldNotDeleteException(
                    __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
                );
            }
            if ($quote->getCouponCode() != '') {
                throw new CouldNotDeleteException(
                    __("The coupon code couldn't be deleted. Verify the coupon code and try again.")
                );
            }
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
