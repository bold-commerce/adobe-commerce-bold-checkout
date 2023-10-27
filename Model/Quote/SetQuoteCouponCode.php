<?php
declare(strict_types=1);

namespace Bold\Checkout\Model\Quote;

use Bold\Checkout\Api\Data\Quote\ResultInterface;
use Bold\Checkout\Api\Quote\SetQuoteCouponCodeInterface;
use Bold\Checkout\Model\Quote\Result\Builder;
use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\ResourceModel\Quote;

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
     * @var Quote
     */
    private $quoteResource;

    /**
     * @param Builder $quoteResultBuilder
     * @param LoadAndValidate $loadAndValidate
     * @param Quote $quoteResource
     */
    public function __construct(
        Builder $quoteResultBuilder,
        LoadAndValidate $loadAndValidate,
        Quote $quoteResource
    ) {
        $this->quoteResultBuilder = $quoteResultBuilder;
        $this->loadAndValidate = $loadAndValidate;
        $this->quoteResource = $quoteResource;
    }

    /**
     * @inheritdoc
     */
    public function setCoupon(string $shopId, int $cartId, string $couponCode): ResultInterface
    {
        try {
            $quote = $this->loadAndValidate->load($shopId, $cartId);
            if (!$quote->getItemsCount()) {
                throw new NoSuchEntityException(__('The "%1" Cart doesn\'t contain products.', $cartId));
            }
            if (!$quote->getStoreId()) {
                throw new NoSuchEntityException(__('Cart isn\'t assigned to correct store'));
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);
            try {
                $quote->setCouponCode($couponCode);
                $quote->collectTotals();
                $this->quoteResource->save($quote);
            } catch (LocalizedException $e) {
                throw new CouldNotSaveException(__('The coupon code couldn\'t be applied: ' . $e->getMessage()), $e);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __("The coupon code couldn't be applied. Verify the coupon code and try again."),
                    $e
                );
            }
            if ($quote->getCouponCode() !== $couponCode) {
                throw new NoSuchEntityException(__("The coupon code isn't valid. Verify the code and try again."));
            }
        } catch (Exception $e) {
            return $this->quoteResultBuilder->createErrorResult($e->getMessage());
        }
        return $this->quoteResultBuilder->createSuccessResult($quote);
    }
}
